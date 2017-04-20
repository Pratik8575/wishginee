<?php

namespace Wishginee\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
use Wishginee\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Checks Whether the user is super user.
     * @param User $user
     * @param $abilities
     * @return bool
     */
    public function before(User $user, $abilities){
        if($user->group == "SU"){
            return true;
        }
    }


    /**
     * Checks that the user itself have update permission.
     * @param $user
     * @param User $subject
     * @return bool
     */
    public function update(User $user, User $subject){
        return ($user->_id == $subject->_id);
    }
    
    public function approveUser(User $user, User $subject){
        return $user->group == "SU";
    }

    /**
     * Only Super User can update the User Group
     * @param $user
     * @param User $subject
     * @return bool
     */
    public function updateGroup(User $user, User $subject){
        return $user->group == "SU";
    }
    
    public function addFounders(User $user, User $subject){
        return in_array($user->group, ["SC","HH","NGO"]); 
    }

    /**
     * Only Super User and User itself have delete permission
     * @param $user
     * @param User $subject
     * @return bool
     */
    public function delete(User $user, User $subject){
        return $user->_id == $subject->_id;
    }

    /**
     * Only SU can refund the donations
     * @param User $user
     * @return bool
     */
    public function refund(User $user){
        return $user->group == "SU";
    }
    
}
