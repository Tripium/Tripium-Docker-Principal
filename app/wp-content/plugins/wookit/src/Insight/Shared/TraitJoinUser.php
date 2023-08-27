<?php

namespace WooKit\Insight\Shared;

use WooKit\Insight\Shared\Query\QueryBuilder;
trait TraitJoinUser
{
    public function setJoin(): QueryBuilder {
        global $wpdb;
        $this->join = " JOIN " . $wpdb->users . " as user ON (user.ID = tblTarget.userID)";

        return $this;
    }
}
