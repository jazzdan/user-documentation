<?hh // strict

use HHVM\UserDocumentation\BuildPaths;
use HHVM\UserDocumentation\APIIndex;
use HHVM\UserDocumentation\APIType;
use HHVM\UserDocumentation\HTMLFileRenderable;

final class APIPageController extends WebPageController {
  protected string $type = '';
  protected string $api = '';
  
  public function __construct(
    private ImmMap<string,string> $parameters,
  ) {
    parent::__construct($parameters);
    $this->type = $this->getRequiredStringParam('type');
    $this->api = $this->getRequiredStringParam('api');
  }
  
  protected async function getTitle(): Awaitable<string> {
    return $this->api;
  }

  protected async function getBody(): Awaitable<XHPRoot> {
    return 
      <div class="referencePageWrapper">
          {$this->getInnerContent()}
      </div>;
  }
  
  protected function getSideNav(): XHPRoot {
    $type = $this->getType();
    $title = ucwords($type.' Reference');    
    $apis = APIIndex::getReferenceForType($type);
    $sub_list = <ul class="subList" />;
    $parent_type_url = sprintf(
      "/hack/reference/%s/",
      $type,
    );
    
    foreach ($apis as $api => $page) {
      $item_url = sprintf(
        "/hack/reference/%s/%s/",
        $type,
        $api,
      );

      $sub_list_item =
        <li class="subListItem">
          <h5><a href={$item_url}>{$api}</a></h5>
        </li>;
          
      if ($this->api === $api) {
        $sub_list_item->addClass("itemActive");
      }
      
      $sub_list->appendChild($sub_list_item);
    }
    
    $type_list = <x:frag />;
    
    foreach (APIType::getValues() as $api_type) {    
      $type_url = sprintf(
        "/hack/reference/%s/",
        $api_type,
      );
      $type_title = ucwords($api_type.' Reference');    
      if ($api_type !== $type) {
        $type_list->appendChild(
          <li><h4><a href={$type_url}>{$type_title}</a></h4></li>
        );
      }
    }

    return
      <div class="navWrapper guideNav">
        <ul class="navList apiNavList">
          <li>
            <h4><a href={$parent_type_url}>{$title}</a></h4>
            {$sub_list}
          </li>
          {$type_list}
        </ul>
      </div>;
  }
  
  protected function getInnerContent(): XHPRoot {
    return self::invariantTo404(() ==> {
      $path = APIIndex::getFileForAPI(
        $this->getRequiredStringParam('type'),
        $this->getRequiredStringParam('api'),
      );
      return 
        <div>{new HTMLFileRenderable($path, BuildPaths::APIDOCS_HTML)}</div>;
    });
  }

  <<__Memoize>>
  private function getType(): APIType {
    return APIType::assert(
      $this->getRequiredStringParam('type')
    );
  }
}
