<?php

namespace App\Http\Controllers;

use App\Users;
use App\Customer;
use App\Fax;
use App\Government;
use App\Helpers\FaxApi;
use App\Helpers\Postal;
use App\Helpers\PostBode;
use App\IpList;
use App\Jobs\SendFax;
use App\ManualRequest;
use App\Customer_reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Session;
use App\Helpers\PdfGenerator;
use App\Mail\FaxSent;
use App\Mail\NotifyDelayCustomer;
use App\Mail\NotifyDelayAdmin;
use App\Mail\NotifyErrorAdmin;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FaxController extends Controller
{

	public function index()
	{
		return view('index');
	}

	public function store(Request $request)
	{
		$validatedData = $request->validate([
			'customer_postal' => 'required',
			'first_name' => 'required',
			'last_name' => 'required',
			'gender' => 'required',
			'phone' => 'required',
			'home_num' => 'required',
			'customer_email' => 'required',
			'customer_address' => 'required',
			'customer_city' => 'required',
			'bank_account' => 'required',
			'name' => 'required',
			'department' => 'required',
			'email' => 'required',
			'fax' => 'required',
			'address' => 'required',
			'postal' => 'required',
			'city' => 'required',
			'date' => 'required',
			'letter_received' => 'required',
			'applied_for' => 'required',
		]);

		$fax_number = $request->fax;
		if (strlen($fax_number) == 10) {
			$fax_number = substr($fax_number, 1);
			$fax_number = "31" . $fax_number;
		}

		Session::put([
			'customer_postal' => $request->customer_postal,
			'first_name' => $request->first_name,
			'last_name' => $request->last_name,
			'gender' => $request->gender,
			'phone' => $request->phone,
			'home_num' => $request->home_num,
			'customer_email' => $request->customer_email,
			'customer_address' => $request->customer_address,
			'customer_city' => $request->customer_city,
			'notes' => $request->notes,
			'bank_account' => $request->bank_account,
			'name' => $request->name,
			'department' => $request->department,
			'email' => $request->email,
			'fax' => $fax_number,
			'address' => $request->address,
			'postal' => $request->postal,
			'city' => $request->city,
			'date' => $request->date,
			'letter_received' => $request->letter_received,
			'applied_for' => $request->applied_for,
		]);

		return redirect('../upload');
	}

	public function ValidatePostal( Request $request )
	{
		return Postal::Validate($request);
	}

	public function getForm()
	{
		Session::put([
			'form_type' => 'form_ingebreke'
		]);
		$request_ip = request()->ip();
		$ip = IpList::where('ip', '=', $request_ip)->first();
		if( $ip != null && $ip->status != 'allow'){
			if($ip->status == 'disallow') {
				$date = new \DateTime();
				$date->modify('-1 day');
				$formatted_date = $date->format('Y-m-d H:i:s');

				$count = Customer::where([['ip', '=', $request_ip], ['created_at', '>',$formatted_date]])->count();
				if($count > 2){
					return response()->json([
						"result" => "block",
						"message" => "U heeft het systeem twee keer gebruikt.<br/>Als u meer procedures wilt starten kunt u een berichtje sturen naar support@beslisapp.nl."
					]);
				}
			}
			else if ($ip->status == 'block'){
				return response()->json([
					"result" => "block",
					"message" => "U heeft het systeem twee keer gebruikt.<br/>Als u meer procedures wilt starten kunt u een berichtje sturen naar support@beslisapp.nl."
				]);
			}
		}
		$municipalities = DB::table('municipality')->get();
		return response()->json([
			'app_type' => Session::get('app_type'),
			'app_data' => Session::get('app_data'),
			'request_date' => Session::get('request_date'),
			'letter_received' => Session::get('letter_received'),
			'letter_weeks' => Session::get('letter_weeks'),
			'letter_days' => Session::get('letter_days'),
			'subject' => Session::get('subject'),
			'municipality' => Session::get('municipality'),
			'municipalities' => compact('municipalities'),
		], 200);
	}

	public function saveForm(Request $request)
	{
		Session::put([
			'app_type' => $request->app_type,
			'app_data' => $request->app_data,
			'request_date' => $request->request_date,
			'letter_received' => $request->letter_received,
			'letter_weeks' => $request->letter_weeks,
			'letter_days' => $request->letter_days,
			'subject' => $request->subject,
			'feature' => $request->feature,
			'municipality' => $request->municipality,
		]);
		return response()->json([
			"result" => "success",
			"message" => "Saved successfully"
		]);
	}
}
