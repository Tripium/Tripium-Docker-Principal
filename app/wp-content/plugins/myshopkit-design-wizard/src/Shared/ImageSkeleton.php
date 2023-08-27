<?php


namespace MyshopKitDesignWizard\Project\Templates;


use MyshopKitDesignWizard\Shared\Project\IPluckHandler;

class ImageSkeleton extends PostSkeleton {
	/**
	 * @var array $aSizes
	 */
	private $aSizes;

	protected static $self;

	/**
	 * @return IPluckHandler
	 */
	public static function init(): IPluckHandler {
		if ( self::$self ) {
			return self::$self;
		}

		self::$self = new self();

		return self::$self;
	}

	/**
	 * @return array
	 */
	public function getWidthHeight(): array {
		if ( isset( $this->aSizes[ $this->getId() ] ) || ! empty( $this->aSizes[ $this->getId() ] ) ) {
			return $this->aSizes[$this->getId()];
		}

		$aSizes = wp_get_attachment_image_src( $this->getId(), 'full' );
		if ( empty( $aSizes ) || is_wp_error( $aSizes ) ) {
			$this->aSizes = [
				'url'    => '',
				'height' => 0,
				'width'  => 0
			];
		} else {
			$this->aSizes[ $this->getId() ] = [
				'height' => $aSizes[2],
				'width'  => $aSizes[1],
				'url'    => $aSizes[0]
			];
		}

		return $this->aSizes[ $this->getId() ];
	}


	public function getWidth(): int {
		return $this->getWidthHeight()['width'];
	}

	public function getHeight(): int {
		return $this->getWidthHeight()['height'];
	}

	public function getUrl(): string {
		return $this->getWidthHeight()['url'];
	}
}
