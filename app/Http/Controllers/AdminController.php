<?php

namespace Wishginee\Http\Controllers;

use Illuminate\Http\Request;

use Wishginee\Campaign;
use Wishginee\Donation;
use Wishginee\Event;
use Wishginee\Form;
use Wishginee\Foundation\Response;
use Wishginee\Http\Requests;
use Wishginee\User;

class AdminController extends Controller
{
    /**
     * Get all the campaigns details for admin.
     * @return Response
     */
    public function getAllCampaignsDetails(){
        $totalCampaigns = Campaign::query()->get()->count();
        $approvedCampaigns = Campaign::query()->where('status', 'approved')->get();
        $pendingCampaigns = Campaign::query()->where('is_approval', true)->where('status', 'pending')->get();
        $savedCampaigns = Campaign::query()->where('is_approval', null)->where('status', 'pending')->get();
        $campaigns = Campaign::query()->get();
        $totalDonation = 0;
        foreach ($campaigns as $campaign){
            $totalDonation += $campaign->fund_raised;
        }
        $response = [];
        return Response::raw(200, array_merge($response,["campaigns" => $campaigns, "total_campaigns" => $totalCampaigns, "approved_campaigns" => $approvedCampaigns, "pending_campaigns" => $pendingCampaigns,"saved_campaigns" => $savedCampaigns, "total_donation" => $totalDonation]));
    }

    /**
     * Get all the events details for admin.
     * @return Response
     */
    public function getAllEventsDetails(){
        $totalEvents = Event::query()->get()->count();
        $approvedEvents = Event::query()->where('status', 'approved')->get();
        $pendingEvents = Event::query()->where('is_approval', true)->where('status', 'pending')->get();
        $savedEvents = Event::query()->where('is_approval', null)->where('status', 'pending')->get();
        $events = Event::query()->get();
        $totalDonation = 0;
        foreach ($events as $event){
            $totalDonation += $event->fund_raised;
        }
        $response = [];
        return Response::raw(200, array_merge($response,["events" => $events, "total_events" => $totalEvents, "approved_events" => $approvedEvents, "pending_events" => $pendingEvents, "saved_events" => $savedEvents, "total_donation" => $totalDonation]));
    }

    public function getAllUsersDetails(){
        $ngos = User::query()->where('group', 'NGO')->get();
        $socialCorps =  User::query()->where('group', 'SC')->get();
        $helpingHands = User::query()->where('group', 'HH')->get();
        $superUsers = User::query()->where('group', 'SU')->get();
        $publicUsers = User::query()->where('group', 'PU')->get()->count();
        $unapprovedNGO = User::query()->where('group', 'NGO')->where('is_approved', false)->get()->count();
        $unapprovedSC = User::query()->where('group', 'SC')->where('is_approved', false)->get()->count();
        $unapprovedHH = User::query()->where('group', 'HH')->where('is_approved', false)->get()->count();

        return Response::raw(200, ["ngos" => $ngos, "social_corporates" => $socialCorps, "helping_hands" => $helpingHands, "super_users" => $superUsers, "public_users" => $publicUsers, "unapproved_ngos" => $unapprovedNGO, "unapproved_sc" => $unapprovedSC, "unapproved_HH" => $unapprovedHH]);
    }

    public function getAllDonationDetails(){
        $donations = Form::query()->get();
        $totalDonations = 0;
        foreach ($donations as $donation){
            $totalDonations += $donation->amount;
        }

        $eventDonations = Form::query()->where('donee_type', 'event')->get(['amount']);
        $eventDonationCount = 0;
        foreach ($eventDonations as $eventDonation){
            $eventDonationCount += $eventDonation->amount;
        }

        $campaignDonations = Form::query()->where('donee_type', 'campaign')->get(['amount']);
        $campaignDonationCount = 0;
        foreach ($campaignDonations as $campaignDonation){
            $campaignDonationCount += $campaignDonation->amount;
        }

        $ngoDonations = Form::query()->where('to_user_type', 'NGO')->get(['amount']);
        $ngoDonationCount = 0;
        foreach ($ngoDonations as $ngoDonation){
            $ngoDonationCount += $ngoDonation->amount;
        }

        $hhDonations = Form::query()->where('to_user_type', 'HH')->get(['amount']);
        $hhDonationCount = 0;
        foreach ($hhDonations as $hhDonation){
            $hhDonationCount += $hhDonation->amount;
        }

        $scDonations = Form::query()->where('from_user_type', 'SC')->get(['amount']);
        $scDonationCount = 0;
        foreach ($scDonations as $scDonation){
            $scDonationCount += $scDonation->amount;
        }


        $puDonations = Form::query()->where('from_user_type', 'PU')->orWhere('from_user_type', 'SU')->get(['amount']);
        $puDonationCount = 0;
        foreach ($puDonations as $puDonation){
            $puDonationCount += $puDonation->amount;
        }

        return Response::raw(200,
            array_merge([
                "total_donations" => $totalDonations,
                "event_donations" => $eventDonationCount,
                "campaign_donations" => $campaignDonationCount,
                "ngo_donations" => $ngoDonationCount,
                "hh_donations" => $hhDonationCount,
                "sc_donations" => $scDonationCount,
                "pu_donations" => $puDonationCount
            ])
        );
    }
}
