<?php namespace App\Repositories\Eloquent;

use App\Repositories\TicketInterface;
use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Bosnadev\Repositories\Eloquent\Repository;
use App\TicketAction;
use Carbon\Carbon;

class TicketRepository extends Repository implements TicketInterface {

	public function model() {
        return 'App\Ticket';
    }

    /**
	 * Create a ticket and/or a reply/comment action
	 * 		
	 * @param  array $attrs [user_id, auth_id, dept_id, title, body, [priority, staff_id, status]]
	 * @return array 
	 */
	public function create(array $data) 
	{
		// $array = array_add($data, 'last_action_at', Carbon::now());
		
		//create ticket
		$ticket = $this->model->create(array_merge($data, ['status' => 'new']));

		$action = $this->createTicketAction([
			'user_id' => $data['auth_id'], 
			'type' => 'create', 
			'title' => $data['title'], 
			'body' => $data['body']
		]);

		$ticket->actions()->save($action);

		return $ticket;
	}

    public function paginateByRequest($perPage = 1, $columns = ['*'])
    {	
    	$this->model = $this->model->select(
    			'tickets.*', 
    			'ticket_actions.title as title', 
    			'users.display_name as user_display_name', 
    			'su.display_name as staff_display_name'
    		)
			->join('users', 'users.id', '=', 'tickets.user_id')
			->join('staff', 'staff.id', '=', 'tickets.staff_id')
			->join('users as su', 'su.id', '=', 'staff.user_id')
			->join('ticket_actions','ticket_actions.ticket_id', '=', 'tickets.id')
			->where('ticket_actions.type', 'create');

    	$this->pushCriteria(new Criteria\RequestSort)
    		->pushCriteria(new Criteria\RequestCreatedAtRange)
    		->pushCriteria(new Criteria\RequestSearchTickets)
    		->pushCriteria(new Criteria\Request('status'))
    		->pushCriteria(new Criteria\Request('priority'))
    		->pushCriteria(new Criteria\Request('dept_id'))
    		->pushCriteria(new Criteria\Request('staff_id'));

    	return parent::paginate($perPage);
    }

    protected function createTicketAction(array $data) 
    {
    	return new TicketAction($data);
    }



	// public function buildUpdateByReply() 
	// {
	// 	return [

	// 	];
	// }

	/**
	 * Update ticket by a comment ticket action.
	 * 
	 * @param  array $action App\TicketAction::toArray()
	 * @return App\Ticket
	 */
	// public function updateByComment(TicketAction $action) 
	// {
	// 	$ticket = parent::update([
	// 			'last_action_at' => $action->created_at,
	// 			'hours' => 'hours + ' . $action->hours
	// 		], $action->ticket_id);

	// 	return $ticket;
	// }
}