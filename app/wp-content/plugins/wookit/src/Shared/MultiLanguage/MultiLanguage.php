<?php

namespace WooKit\Shared\MultiLanguage;

use Exception;

class MultiLanguage {
	protected static array          $aDataConfig = [];
	protected static ?MultiLanguage $self        = null;

	public static function setLanguage( ?string $locale ): ?MultiLanguage {
		self::$aDataConfig = [];

		if ( ! self::$self ) {
			self::$self = new self();
		}

		$aSymbol = include plugin_dir_path( __FILE__ ) . 'Config/SymbolLanguage.php';
		if ( empty( $locale ) ) {
			$language = $aSymbol['en'];
		} else {
			$language = array_key_exists( $locale, $aSymbol ) ? $aSymbol[ $locale ] : $aSymbol['en'];
		}

		self::$aDataConfig = include plugin_dir_path( __FILE__ ) . 'Config/Languages/' . $language . '.php';
		self::$aDataConfig = apply_filters(
			WOOKIT_PREFIX . 'Shared/MultiLanguage/Config/Languages/' . $language,
			self::$aDataConfig,
			$language
		);
		return self::$self;
	}

	/**
	 * @throws Exception
	 */
	public function getMessage( string $symbolMessage ) {
		if ( array_key_exists( $symbolMessage, self::$aDataConfig ) ) {
			return self::$aDataConfig[ $symbolMessage ];
		} else {
			return $symbolMessage;
		}
	}
}
