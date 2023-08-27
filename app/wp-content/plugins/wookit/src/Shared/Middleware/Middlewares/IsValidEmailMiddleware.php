<?php


namespace WooKit\Shared\Middleware\Middlewares;


use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use WooKit\Illuminate\Message\MessageFactory;
use WooKit\Shared\MultiLanguage\MultiLanguage;

class IsValidEmailMiddleware implements IMiddleware {
	use TraitLocale;

	private function isWhiteListEmail( $email ): bool {
		$aBlackLists = include plugin_dir_path( dirname( __FILE__ ) ) . 'Configs/EmailsBlackList.php';

		foreach ( $aBlackLists as $blackListDomain ) {
			if ( strpos( $email, $blackListDomain ) !== false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @throws \Exception
	 */
	public function validation( array $aAdditional = [] ): array {
		if ( ! isset( $aAdditional['email'] ) || empty( $aAdditional['email'] ) ) {
			return MessageFactory::factory()->error(
				MultiLanguage::setLanguage( $this->getMiddlewareLocale( $aAdditional ) )->getMessage( 'invalidEmail' ),
				400
			);
		}


		$oValidator           = new EmailValidator();
		$aMultipleValidations = new MultipleValidationWithAnd( [
			new RFCValidation(),
			new DNSCheckValidation()
		] );

		$status = $oValidator->isValid( $aAdditional['email'], $aMultipleValidations ); //true

		if ( $status ) {
			$status = $this->isWhiteListEmail( $aAdditional['email'] );
		}

		if ( ! $status ) {
			return MessageFactory::factory()->error(
				MultiLanguage::setLanguage( $this->getMiddlewareLocale( $aAdditional ) )->getMessage( 'invalidEmail' ),
				400
			);
		}

		return MessageFactory::factory()->success( 'Passed' );
	}
}
