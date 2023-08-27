<?php


namespace MyshopKitDesignWizard\Shared\Middleware\Middlewares;


use Exception;
use MyshopKitDesignWizard\Illuminate\Message\MessageFactory;


class IsPostExistMiddleware implements IMiddleware
{
    protected array $aStatusBadge = ['publish', 'draft','trash'];

    /**
     * @throws Exception
     */
    public function validation(array $aAdditional = []): array
    {
        $postID = $aAdditional['postID'] ?? '';
        if (empty($postID)) {
            throw new Exception('Sorry, the id is required', 400);
        }
        if (!in_array(get_post_status($postID), $this->aStatusBadge)) {
            throw new Exception('Sorry, the project doest not exist at the moment', 400);
        }

        return MessageFactory::factory()->success('Passed');
    }
}
