<?php

namespace Wishginee\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Wishginee\Campaign;
use Wishginee\User;

class CampaignPolicy
{
    use HandlesAuthorization;

    /**
     * Checks if group is SU then returns true for all the policy methods.
     * @param User $user
     * @param $abilities
     * @return bool
     */
    public function before(User $user, $abilities){
        if($user->group == "SU" && $user->is_approved){
            return true;
        }
    }

    /**
     * Campaigns can be created only by those who have groups NGO, HH, PU
     * @param User $user
     * @return bool
     */
    public function create(User $user){
        return in_array($user->group, ["NGO", "HH"]);
    }

    /**
     * Only SU has permission to update the status of the campaign
     * @param User $user
     * @param User $subject
     * @return bool
     */
    public function updateStatus(User $user, User $subject){
        return $user->group == "SU";
    }


    /**
     * Only SU and the creator of the campaign has update permission
     * @param User $user
     * @param Campaign $campaign
     * @return bool
     */
    public function update(User $user, Campaign $campaign){
        return ($user->_id == $campaign->user_id) && ($campaign->status == Campaign::STATUS[0]);
    }


    /**
     * Only SU and the creator of the campaign has delete permission
     * @param User $user
     * @param Campaign $campaign
     * @return bool
     */
    public function delete(User $user, Campaign $campaign){
        return (($user->_id == $campaign->user_id) ||  ($user->group == "SU")) && ($campaign->status == Campaign::STATUS[0]);
    }
    
}
