<?php

namespace WooKit\MailServices\Shared;

trait TraitGenerateRestEndpoint {

	public function  getListIdEndPoint(): string {
		return 'me/mailservices/lists';
	}

	public function getSaveEmailEndPoint(): string {
		return 'me/mailservices/lists/members';
	}

	public function getChangeServiceStatusEndPoint(): string {
		return 'me/mailservices/status';
	}

	public function getMailServiceEndpoint(): string {
		return 'me/mailservices';
	}
}
