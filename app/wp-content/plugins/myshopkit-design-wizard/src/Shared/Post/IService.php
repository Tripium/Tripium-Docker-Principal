<?php


namespace MyshopKitDesignWizard\Shared\Post;


interface IService {
	/**
	 * Định nghĩa tất cả các fields cần update tại đây
	 * Mỗi field được định nghĩa trong 1 array.
	 *
	 * @return array
	 */
	public function defineFields(): array;

	/**
	 * Trong dữ liệu post lên, lọc những dữ liệu được định nghĩa ở 1
	 *
	 * @param array $aRawData
	 *
	 * @return IService
	 */
	public function setRawData( array $aRawData ): IService;

	/**
	 * Tiến hành validate dữ liệu: Ép kiểu hoặc tiến hành Sanitize nếu cần.
	 * @return IService
	 */
	public function validateFields(): IService;

	/**
	 * Tiến hành update dữ liêu. Trả về trạng thái thất bại, thành công thông qua MessageFactory
	 *
	 * @return array{id: string}
	 */
	public function performSaveData(): array;
}
