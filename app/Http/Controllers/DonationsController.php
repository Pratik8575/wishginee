<?php

namespace Wishginee\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Wishginee\Campaign;
use Wishginee\Donation;
use Wishginee\Event;
use Wishginee\Feed;
use Wishginee\Form;
use Wishginee\Foundation\Razorpay;
use Wishginee\Foundation\Response;
use Wishginee\Http\Requests;
use Wishginee\User;

class DonationsController extends Controller
{
    protected $razorpay;

    /**
     * DonationsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->razorpay = new Razorpay();
    }

    /**
     * Captures the payment
     * @param Request $request
     * @return Response
     */
    public function capture(Request $request){
        $validation = \Validator::make($request->all(),[
            'donee_type' => 'string|required|in:event,campaign',
            'donee_id' => 'required',
            'donee_name' => 'string|required',
            'payment_id' => 'string|required',
            'amount' => 'required'
        ]);

        if($validation->fails()){
            return Response::error(422, $validation->errors());
        }
        
        $paymentId = $request->get('payment_id');
        $amount = $request->get('amount');
        if($request->get('donee_type') == "event"){
            $donee = Event::query()->findOrFail($request->get('donee_id'));
        }else{
            $donee = Campaign::query()->findOrFail($request->get('donee_id'));
        }


        $payment = $this->razorpay->payment->fetch($paymentId);
        if($payment->status == "authorized"){
            $response = $payment->capture(array('amount' => $amount));
            if(is_null($response->error_code) && $response->amount == $amount){
                $donation = new Donation();
                $donation->fillable(["date", "payment_id","user_id", "user_name", "user_email", "donee_id", "donee_name", "amount", "donee_type"]);
                $donation->payment_id = $paymentId;
                $donation->user_id = auth()->user()->_id;
                $donation->user_name = auth()->user()->getNameAttribute();
                $donation->user_email = auth()->user()->email;
                $donation->donee_id = $request->get('donee_id');
                $donation->donee_name = $request->get('donee_name');
                $donation->amount = ($response->amount)/100;
                $donation->donee_type = $request->get('donee_type');
                $donation->date = Carbon::now();
                $donation->save();

                $form = new Form();
                $form->fillable(["date", "user_id", "user_name","donee_id", "donee_name", "amount", "note"]);
                $form->user_id = auth()->user()->_id;
                $form->user_name = auth()->user()->getNameAttribute();
                $form->donee_id = $request->get('donee_id');
                $form->donee_type = $request->get('donee_type');
                $form->amount = ($response->amount)/100;
                $form->to_user_type = $donee->user->group;
                $form->from_user_type = auth()->user()->group;
                $form->note = "Successfully Donated";
                $form->date = Carbon::now();
                $form->save();
                
                $donee->fund_raised += ($response->amount)/100;
                $donee->save();

                //Generate Public Feed

                $feed = new Feed();
                $feed->user_id = auth()->user()->_id;
                $feed->user_name = auth()->user()->getNameAttribute();
                $feed->type_id = $donee->_id;
                $feed->type_name = $donee->name;
                if($request->get('donee_type') == "campaign"){
                    $feed->text = ' donated to campaign ';
                    $feed->type = 'campaigns';
                }else{
                    $feed->text = ' donated to event ';
                    $feed->type = 'events';
                }
                $feed->cover_photo = $donee->cover_photo;
                $feed->category = $donee->category;
                $feed->fund_needed = $donee->fund_needed;
                $feed->fund_raised = $donee->fund_raised;
                $feed->email = $donee->contact_email;
                $feed->mobile = $donee->contact_mobile;
                $feed->date = Carbon::now();
                $feed->save();

                //This is the best place where we can send E-mail notification or receipt to the user.

                return Response::raw(200,["message" => "Donated Successfully. Thanks for your donation!"]);
            }
        }
    }


    /**
     * Refunds the amount to particular user.
     * @param $userId
     * @param $paymentId
     * @return Response
     */
    public function refund($userId, $paymentId){
        $this->authorize('refund', User::class);

        $user = User::query()->findOrFail($userId);
        $payment = $this->razorpay->fetch($paymentId);

        if($payment->status == "captured"){
            $response = $payment->refund();

            $donation = Donation::query()->where('payment_id', $paymentId)->first();
            $doneeId = $donation->donee_id;
            $doneeType = $donation->donee_type;
            $donation->delete();

            if($doneeType == "event"){
                $donee = Event::query()->findOrFail($doneeId);
            }else{
                $donee = Campaign::query()->findOrFail($doneeId);
            }
            
            $donee->fund_raised -= $response->amount;
            $donee->save();

            $form = new Form();
            $form->fillable(["date", "user_id", "user_name","donee_id", "donee_name", "amount", "note"]);
            $form->user_id = $user->_id;
            $form->user_name = $user->first_name.' '.$user->last_name;
            $form->amount = -$response->amount;
            $form->note = "Refunded";
            $form->date = Carbon::now();
            $form->save();

            
            return Response::raw(200, ["message" => "Amount Refunded successfully."]);
        }
    }
}
