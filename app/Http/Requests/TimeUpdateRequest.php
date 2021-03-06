<?php namespace App\Http\Requests;

use App\Repositories\Eloquent\TimeLogRepository;
use Auth;

class TimeUpdateRequest extends FormRequest {	

	protected $rules = [
        'type' => ['in:vacation,holiday,sick,other'],
        'message' => ['min:8'],
		'hours' => ['numeric'],
		'time_at' => ['date_format:m/d/Y']
    ];

    public function __construct(TimeLogRepository $time) 
    {
    	$this->time = $time;
    }

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		$entry = $this->time->find($this->segment(count($this->segments())));
		return $entry->user_id == Auth::user()->id;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{

		return $this->rules;
	}

}
