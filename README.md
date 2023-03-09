# pw-components-php-dev
## Requirements
 * PHP version 8.0 ou supérieure

## Installation

* Avec composer:
```
composer require passion-web/pw-components-php-dev
```

* Avec composer.phar:
```
php composer.phar require passion-web/pw-components-php-dev
```

## Utilisation
### Recaptcha
#### Importation de la classe
```php
use Pw\Recaptcha\Recaptcha
```

#### Instanciation
```php
$recaptcha = new Recaptcha([]);
```

#### Exemple d’utilisation
```php
namespace App\Service;

use Pw\Recaptcha\Recaptcha;

class ReCAPTCHAService {

	private $secret;
    private $recaptcha_key;
    private $recaptcha_score_threshold;

    public function __construct( 
        string $recaptcha_secret,
        string $recaptcha_key,
        string $RECAPTCHA_SCORE_THRESHOLD
    ){
    	$this->secret = $recaptcha_secret;
        $this->recaptcha_key = $recaptcha_key;
        $this->recaptcha_score_threshold = $RECAPTCHA_SCORE_THRESHOLD;
    }

    /**
     * @param string $recaptcha_token
     * @return boolean
     */
    public function isValidReCaptcha($recaptcha_token){
        if(!$recaptcha_token){
            return false;
        }

        $options = [
            "secret" => $this->secret,
            "recaptcha_key" => $this->recaptcha_key,
            "recaptcha_score_threshold" => $this->recaptcha_score_threshold,
        ];

        $recaptcha = new Recaptcha($options);
        $recaptchaData = null;

        $isValid = $recaptcha->isValidReCaptcha(
            $recaptcha_token,  
            $recaptchaData
        );

        return $isValid;
    }

}
```
###### On peut utiliser partout la méthode `isValidReCaptcha` avec `recaptcha_token` en paramètre.

###### Si on veut récupérer le `data` retourné par `reCAPTCHA` en cas d’erreur ou du succès, on peut l’avoir avec `recaptchaData`


### RateLimiter
#### Importation de la classe
```php
use Pw\RateLimiter\RateLimiter
```

#### Instanciation
```php
$rateLimiter = new RateLimiter()
```

#### Exemple d’utilisation
```php
namespace App\Service;

use Pw\RateLimiter\RateLimiter;
use Symfony\Component\RateLimiter\RateLimiterFactory ;

class RateLimiterService {

    private $anonymousApiLimiter;

    public function __construct(
        RateLimiterFactory  $anonymousApiLimiter,
    ){
        $this->anonymousApiLimiter = $anonymousApiLimiter;
    }

    public function rateLimiter()
    {
        $rateLimiter = new RateLimiter($this->anonymousApiLimiter);
        $isLimit = $rateLimiter->isConsumeRateLimiter();

        return $isLimit;
    }
}
```



### DataTable
#### Importation de la classe
```php
use Pw\DataTable\ApiDataTable
```

#### Configuration 
Modification du fichier services.yaml pour autoriser le autowire de l’apiDataTable autowire: true
```yaml
    Pw\DataTable\ApiDataTable:
        # redundant thanks to _defaults, but value is overridable on each service
        autowire: true
```
Modification du fichier bundle.php 
```php
    return [
   ...
    Pw\DataTable\ApiDataTable::class => ['dev' => true, 'test' => true],
];
```

#### Exemple d’utilisation
```php
namespace App\Service;


use App\Entity\Utilisateur;
use Pw\DataTable\ApiDataTable;

class ApiDataTableService {
    private $em;
    private $apiDataTable;

    public function __construct(
        EntityManagerInterface $em,
        ApiDataTable  $apiDataTable
    ){
        $this->em = $em;
        $this->apiDataTable = $apiDataTable;
    }

    public function getDataTable()
    {
        $params = [
            "em" => $this->em,
            "query" => [
                "key" => "",
                "page" => "1",
                "limit" => "5",
                "filters" => [],
                "order" => "ASC",
                "order_by" => "name",
            ],
            "entity" => Utilisateur::class
        ];
        $apiDataTable = $this->apiDataTable;
        $result = $apiDataTable->get($params);

        return $result;
    }
}
```

### Params
#### Importation de la classe
```php
use Pw\Params\Params
```

#### Instanciation
```php
$pwParams = new Params()
```

#### Exemple d’utilisation
```php
namespace App\Service;

use Pw\Params\Params;

class FormulaireService {

    public function __construct(){
        $this->pwParams = new Params();
    }

    public function addFormulaire()
    {
        $pwParams = $this->pwParams;
        
        // retrieves $_POST variables 
        $lastname = $pwParams->post($request, "lastname");

        // retrieves  $_GET variables 
        $lastname = $pwParams->get($request, "lastname");

        // get value in associative array by key
        $array = [
            "lastname" => "Rakoto"
        ];
        $lastname = $pwParams->array($array, "lastname");

        return $pwParams->response("ok", "success", []);
    }
}
```

### Generator

#### Configuration 
Modification du fichier services.yaml pour autoriser le autowire de Pw\Command\GeneratorCommand autowire: true
```yaml
    Pw\Command\GeneratorCommand:
        # redundant thanks to _defaults, but value is overridable on each service
        autowire: true
```

#### Exemple d’utilisation
###### Pour créer une page front
```php bin/console pw-generator:generate page front
```
###### Pour créer un API front
```php bin/console pw-generator:generate api front
```
###### Pour créer un service front
```php bin/console pw-generator:generate service front
```

#### Syntaxe
```php bin/console pw-generator:generate <type> <name> <method> [options]```

#### Autres utilisations avec les options 
##### Pour créer une page front avec une méthode index
```
php bin/console pw-generator:generate page front index -r page_front_index -u /page/front/index -t /page/front/index.twig.html --request="GET"
```

##### Pour créer un API front avec une méthode index
```
php bin/console pw-generator:generate api front index -r api_front_index -u /api/front/index --request="POST"
```

##### Pour créer un service front avec les méthodes save, load, list
```
php bin/console pw-generator:generate service front -m "save, load, list"
```

##### Pour avoir de l'aide 
```
php bin/console pw-generator:generate --help
```

##### Utilisation de la commande en mode interactif (type ='page ou service ou api')
```
php bin/console pw-generator:generate <type>
```

