<?php


namespace MyshopKitDesignWizard\Shared\Post\Query;


use MyshopKitDesignWizard\Illuminate\Prefix\AutoPrefix;
use MyshopKitDesignWizard\Illuminate\Upload\TraitHandleThumbnail;
use WP_Post;

class PostSkeleton
{
	use TraitHandleThumbnail;

	protected array   $aPluck
		= [
			'id',
			'label',
			'date',
			'content',
			'status',
			'metadata',
			'taxonomies',
			'postType',
			'thumbnail',
			'thumbnails',
			'color',
			'endpoint',
			'isGlobalTemplate',
			'totalChildren',
			'totalChildrenText',
			'parentInfo',
		];
	protected WP_Post $oPost;

	public function getPostType()
	{
		return AutoPrefix::namePrefix('my_projects');
	}

	public function getParentInfo(): ?array
	{
		$aParentInfo = [];
		$oPostParent = get_post_parent($this->oPost->ID);
		if ($oPostParent instanceof WP_Post) {
			$aParentInfo = [
				'id'            => $oPostParent->ID,
				'label'         => $oPostParent->post_title,
				'totalChildren' => $this->getTotalChildren($oPostParent->ID),
			];
		}
		return (!empty($aParentInfo)) ? $aParentInfo : null;
	}

	public function checkMethodExists($pluck): bool
	{
		$method = 'get' . ucfirst($pluck);

		return method_exists($this, $method);
	}

	public function getId(): int
	{
		return $this->oPost->ID;
	}

	public function getEndpoint(): string
	{
		return 'me/projects/' . $this->oPost->ID . '/detail';
	}

	public function getColor(): string
	{
		$jPostMeta = get_post_meta($this->oPost->ID, AutoPrefix::namePrefix('project_metadata'), true);
		$aPostMeta = json_decode(is_array($jPostMeta) ? '' : $jPostMeta, true);
		return is_array($aPostMeta) && !empty($aPostMeta) ? $aPostMeta['color'] : '';
	}

	public function getTotalChildren($id = 0): int
	{
		return count(get_children([
			'post_parent' => empty($id) ? $this->oPost->ID : $id,
			'post_type'   => $this->getPostType()
		], ARRAY_A));
	}

	public function getLabel(): string
	{
		return $this->oPost->post_title;
	}

	public function totalChildrenText(): string
	{
		return $this->getTotalChildren() . ' template';
	}

	public function getIsGlobalTemplate(): bool
	{
		return false;
	}

	public function getContent(): array
	{
		$jPostMeta = get_post_meta($this->oPost->ID, AutoPrefix::namePrefix('project_content'), true);
		$aPostMeta = json_decode(base64_decode($jPostMeta), true);
		if (empty($aPostMeta)) {
			$aPostMeta = json_decode(stripslashes(base64_decode($jPostMeta)), true);
		}
		return is_array($aPostMeta) ? $aPostMeta : [];
	}

	public function getMetadata(): array
	{
		$jPostMeta = get_post_meta($this->oPost->ID, AutoPrefix::namePrefix('project_metadata'), true);
		$aPostMeta = json_decode($jPostMeta, true);
		return is_array($aPostMeta) ? $aPostMeta : [];
	}

	public function getTaxonomies(): array
	{
		$aData = [];
		$aTags = [];
		$aData['pl_template_category'] = [];
		$jPostMeta = get_post_meta($this->oPost->ID, AutoPrefix::namePrefix('project_taxonomies'), true);
		$aPostMeta = !empty($jPostMeta) ? json_decode($jPostMeta, true) : [];
		$aTagsID = is_array($aPostMeta) && !empty($aPostMeta) ? $aPostMeta['tags'] : [];
		if (!empty($aTagsID)) {
			foreach ($aTagsID as $id) {
				$oTag = get_term($id, AutoPrefix::namePrefix('post_tag'));
				$aTags[] = [
					'label'    => $oTag->name,
					'id'       => $id,
					'endpoint' => 'tags/' . $id
				];
			}
		}
		$aData['tags'] = $aTags;
		return $aData;
	}

	public function getThumbnailIDWithPostID($postID): int
	{
		$jPostMeta = get_post_meta($postID, AutoPrefix::namePrefix('project_thumbnail_id'), true);
		return !empty($jPostMeta) ? json_decode($jPostMeta, true)['id'] : 0;
	}

	public function getThumbnail(): array
	{
		$aThumbnail = [];
		$thumbnailId = $this->getThumbnailIDWithPostID($this->oPost->ID);

		if (!empty($thumbnailId)) {
			$aAttachment = wp_get_attachment_image_src($thumbnailId, 'large');

			$aThumbnail = [
				'id'     => $thumbnailId,
				'url'    => $aAttachment[0] ?? '',
				'height' => $aAttachment[2] ?? 0,
				'width'  => $aAttachment[1] ?? 0
			];
		}
		return $aThumbnail;
	}

	public function getThumbnails(): array
	{
		$aThumbnail = [];
		$thumbnailId = $this->getThumbnailIDWithPostID($this->oPost->ID);
		if (!empty($thumbnailId)) {
			$aThumbnail = $this->getThumbnailDefault($thumbnailId);
		}
		return $aThumbnail ?: [
			'5x5'       => [],
			'full'      => [],
			'large'     => [],
			'medium'    => [],
			'thumbnail' => []
		];
	}

	public function getStatus(): string
	{
		return ($this->oPost->post_status == 'publish') ? 'active' : 'deactive';
	}

	public function getDate(): string
	{
		return (string)strtotime(date(get_option('date_format'), strtotime($this->oPost->post_date)));
	}

	public function setPost(WP_Post $oPost): PostSkeleton
	{
		$this->oPost = $oPost;

		return $this;
	}

	public function getPostData($pluck, array $aAdditionalInfo = []): array
	{
		$aData = [];

		if (empty($pluck)) {
			$aPluck = $this->aPluck;
		} else {
			$aPluck = $this->sanitizePluck($pluck);
		}

		foreach ($aPluck as $pluck) {
			$method = 'get' . ucfirst($pluck);
			if (method_exists($this, $method)) {
				$aData[$pluck] = call_user_func_array([$this, $method], [$aAdditionalInfo]);
			}
		}

		return $aData;
	}

	private function sanitizePluck($rawPluck): array
	{
		$aPluck = is_array($rawPluck) ? $rawPluck : explode(',', $rawPluck);
		return array_map(function ($pluck) {
			return trim($pluck);
		}, $aPluck);
	}
}
