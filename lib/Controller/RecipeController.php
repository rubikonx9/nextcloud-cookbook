<?php
namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\Files\FileInfo;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Controller;

use OCA\Cookbook\Service\RecipeService;

class RecipeController extends Controller {
	private $userId;

	public function __construct($AppName, IDBConnection $db, IRootFolder $root, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
        $this->userId = $UserId;

        $this->service = new RecipeService($root, $UserId, $db);
	}
    
    /**
	 * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function all() {
        $data = $this->service->getAllRecipesInSearchIndex();

        return new DataResponse($data, Http::STATUS_OK, [ 'Content-Type' => 'application/json' ]);
    }
    
    /**
	 * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function keywords() {
        $data = $this->service->getAllKeywordsInSearchIndex();

        return new DataResponse($data, Http::STATUS_OK, [ 'Content-Type' => 'application/json' ]);
    }
    
    /**
	 * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function find() {
        $data = $this->service->findRecipesInSearchIndex(
            isset($_GET['keywords']) ? $_GET['keywords'] : ''
        );

        return new DataResponse($data, Http::STATUS_OK, [ 'Content-Type' => 'application/json' ]);
    }
    
    /**
	 * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function add() {
        if(!isset($_POST['url'])) { throw new \Exception('Field "url" is required'); }

        $recipe_file = $this->service->downloadRecipe($_POST['url']);
        $recipe_json = $this->service->parseRecipeFile($recipe_file);

        return new DataResponse($recipe_json, Http::STATUS_OK, [ 'Content-Type' => 'application/json' ]);
    }

    /**
	 * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function reindex() {
        $this->service->rebuildSearchIndex();
        
        return new DataResponse('Search index rebuilt successfully', Http::STATUS_OK);
    }

    /**
	 * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function image() {
        if(!isset($_GET['recipe'])) { return null; }

        $size = isset($_GET['size']) ? $_GET['size'] : null;

        $file = $this->service->getRecipeImageFileById($_GET['recipe']);

        return new FileDisplayResponse($file, Http::STATUS_OK, [ 'Content-Type' => 'image/jpeg' ]);
    }
}