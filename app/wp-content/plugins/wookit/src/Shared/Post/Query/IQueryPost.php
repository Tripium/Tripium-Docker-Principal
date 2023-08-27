<?php


namespace WooKit\Shared\Post\Query;


interface IQueryPost {
	## Định nghĩa post type. Ví dụ: Nếu bạn implement tại Popup thì xét cứng post type là popup
	public function getPostType(): string;

	## Dữ liệu query thô truyền vào
	public function setRawArgs( array $aRawArgs ): IQueryPost;

	## Phân tích raw args để cho ra purge args
	public function parseArgs(): IQueryPost;

	## trường hợp bạn muốn lấy dữ liệu purge args
	public function getArgs(): array;

	/**
	 * Tiến hành query và trả lại kết quả
	 *
	 * @param PostSkeleton $oPostSkeleton
	 * @param false $isSingle : Trả lại kết quả bọc bởi 1 mảng hoặc flat item
	 * @param string $pluck
	 *
	 * @return array
	 */
	public function query( PostSkeleton $oPostSkeleton, string $pluck = '', bool $isSingle = false ): array;
}
