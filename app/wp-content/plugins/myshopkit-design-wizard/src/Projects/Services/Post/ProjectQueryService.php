<?php


namespace MyshopKitDesignWizard\Projects\Services\Post;


use MyshopKitDesignWizard\Shared\Post\Query\IQueryPost;
use MyshopKitDesignWizard\Shared\Post\Query\QueryPost;

class ProjectQueryService extends QueryPost implements IQueryPost
{
    public function parseArgs(): IQueryPost
    {
        $this->aArgs = $this->commonParseArgs();

        return $this;
    }

}
