<?hh // strict

use HHVM\UserDocumentation\APIIndex;
use HHVM\UserDocumentation\APIType;

enum APIProduct: string as string {
  HACK = 'hack';
}

final class APIListController extends WebPageController { 
  public function __construct(
    private ImmMap<string,string> $parameters,
  ) {
    parent::__construct($parameters);
  }
  
  protected async function getTitle(): Awaitable<string> {
    switch ($this->getProduct()) {
      case APIProduct::HACK:
        return 'Hack APIs';
    }
  }
  
  protected function getInnerContent(): XHPRoot {
    if ($this->parameters->containsKey('type')) {
      $api_type = $this->parameters['type'];
      $apis = Map {
        $api_type => APIIndex::getReferenceForType($api_type),
      };
    } else {
      $apis = Map {};
      foreach (APIType::getValues() as $api_key => $api_type) {
        $apis[$api_type] = APIIndex::getReferenceForType($api_type);
      }
    }

    $root = <div class="referenceList" />;
    foreach ($apis as $type => $api_references) {
      $title = ucwords($type.' Reference');
      $type_list = <ul class="apiList" />;
      foreach ($api_references as $api => $reference) {
        $url = sprintf(
          "/hack/reference/%s/%s/",
          $type,
          $api,
        );
        $type_list->appendChild(
          <li>
            <h4><a href={$url}>{$api}</a></h4>
          </li>
        );
      }

      $root->appendChild(
        <div class="referenceType">
          <h3 class="listTitle">{$title}</h3>
          {$type_list}
        </div>
      );
    }
    return $root;
  }

  protected async function getBody(): Awaitable<XHPRoot> {
    return 
      <div class="apiListWrapper">
        {$this->getInnerContent()}
      </div>;
  }

  <<__Memoize>>
  private function getProduct(): APIProduct {
    return APIProduct::assert(
      $this->getRequiredStringParam('product')
    );
  }
}
