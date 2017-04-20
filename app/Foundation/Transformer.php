<?php
namespace Wishginee\Foundation;

class Transformer{

    /**
     * Transforming some of the entities of the User to Public Entities only
     * @param $user
     * @return array
     */
    public static function userTransform($user){
        $unGuarded = [
            'id' => $user->_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'profile_photo' => $user->profile_photo,
            'cover_photo' => $user->cover_photo,
            'website' => $user->website,
            'city' => $user->city,
            'country' => $user->country,
            'description' => $user->description,
            'group' => $user->group
        ];
        return $unGuarded;
    }

    /**
     * Transforming some of the entities of the Event to Public Entities only
     * @param $event
     * @return array
     */
    public static function eventTransform($event){
        $unGuarded = [
            'id' => $event->_id,
            'user_id' => $event->user_id,
            'name' => $event->name,
            'contact_email' => $event->contact_email,
            'profile_photo' => $event->profile_photo,
            'abstract' => $event->abstract,
            'fund_needed' => $event->fund_needed,
            'fund_raised' => $event->fund_raised,
            'date' => $event->date,
            'time' => $event->time,
            'location' => $event->location,
            'event_by' => $event->event_by
        ];
        return $unGuarded;
    }

    /**
     * Transforming some of the entities of the Campaign to Public Entities only
     * @param $campaign
     * @return array
     */
    public static function campaignTransform($campaign){
        $unGuarded = [
            '_id' => $campaign->_id,
            'user_id' => $campaign->user_id,
            'name' => $campaign->name,
            'contact_email' => $campaign->contact_email,
            'contact_mobile' => $campaign->contact_mobile,
            'profile_photo' => $campaign->profile_photo,
            'cover_photo' => $campaign->cover_photo,
            'abstract' => $campaign->abstract,
            'category' => $campaign->category,
            'fund_needed' => $campaign->fund_needed,
            'fund_raised' => $campaign->fund_raised,
            'story' => $campaign->story,
            'date' => $campaign->date,
            'location' => $campaign->location,
            'campaign_by' => $campaign->campaign_by,
            'status' => $campaign->status
        ];
        return $unGuarded;
    }
    
}