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

### Export Excel
#### Importation de la classe
```php
use Pw\Exports\ExportExcel
```

#### Configuration 
Modification du fichier services.yaml pour autoriser le autowire de l’apiDataTable autowire: true
```yaml
services:
    # ...
    Pw\Exports\ExportExcel:
        autowire: true
```

#### Exemple d’utilisation (simple onglet)
1- Création d’un service d’export 
exemple : UserExportExcelService.php
```php
<?php
namespace App\Service;

use App\Entity\User;
use Pw\Exports\ExportExcel;
use Doctrine\ORM\EntityManagerInterface;

class UserExportExcelService {

    const DICTIONARY = [
        "is_active" => [
            "1" => "Oui",
            "0" => "Non",
            true => "Oui",
            false => "Non",
            null => "Non",
        ],
    ];

    const KEY_LABELS = [
        "id" => "Id",
        "lastname_firstname" => "Nom et Prénom",
        "email" => "Email",
        "phone" => "Téléphone",
        "is_active" => "Valider",
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private ExportExcel $exportExcel
    ){}

    /**
     * public methode to generate an excel file
     * 
     * @return File || null
     */
    public function generateExcel(){

        $onglet_name = 'Liste des utilisateurs' ;
        $KEY_LABELS = self::KEY_LABELS;
        $DICTIONARY = self::DICTIONARY;
        $key_values = [];

        $users = $this->em->getRepository(User::class)->findByParams(["role" => "ROLE_USER"]) ;
        foreach($users as $user){
            $key_values[] = [
                "id" => $user->getId(),
                "lastname_firstname" => implode(" ", [$user->getLastname(), $user->getFirstname()]),
                "email" => $user->getEmail(),
                "phone" => $user->getPhone(),
                "is_active" => $user->isIsActive(),
            ];
        };

        $formated = $this->exportExcel->formatExport(
            $onglet_name,
            $key_values, 
            $KEY_LABELS, 
            $DICTIONARY
        );
        $spreadsheet = $this->exportExcel->setupSpreadSheet($formated, [
            "superHeadSize" => 15,
            "superHeadColor" => "ffffff",
            "superHeadBgColor" => "ff0000",
            "headSize" => 10,
            "headColor" => "000000",
            "headBgColor" => "00ff00",
            "contentSize" => 10,
        ]);

        $excel = $this->exportExcel->buildExcel("liste-utilisateurs.xlsx", $spreadsheet);

        if(is_file($excel)){
            return $excel;
        }

        return null;
    }
}
```
2- Création du contrôleur
exemple : ExportController.php
```php
<?php

namespace App\Controller;

use App\Service\UserExportExcelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends AbstractController
{
    #[Route('/user/export/excel', name: 'user_export_excel')]
    public function userExportExcelAction(
        Request $request,
        UserExportExcelService $userExportExcelService
    ): Response
    {
        $excel = $userExportExcelService->generateExcel();

        $filename = "Liste-utilisateurs.xlsx";
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT;

        $response = new BinaryFileResponse($excel);
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContentDisposition($disposition, $filename);
        return $response;
    }
}
```

#### Exemple d’utilisation (multiple onglet)
1- Création d’un service d’export 
exemple : UserExportExcelService.php
```php
<?php
namespace App\Service;

use App\Entity\User;
use Pw\Exports\ExportExcel;
use Doctrine\ORM\EntityManagerInterface;

class UserExportExcelService {

    const DICTIONARY = [
        "is_active" => [
            "1" => "Oui",
            "0" => "Non",
            true => "Oui",
            false => "Non",
            null => "Non",
        ],
    ];

    const KEY_LABELS = [
        "id" => "Id",
        "lastname_firstname" => "Nom et Prénom",
        "email" => "Email",
        "phone" => "Téléphone",
        "is_active" => "Valider",
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private ExportExcel $exportExcel
    ){}

    /**
     * public methode to generate an excel file
     * 
     * @return File || null
     */
    public function generateExcel(){

        $onglet_name_user = 'Liste des utilisateurs' ;
        $onglet_name_admin = 'Liste des administrateur' ;
        $KEY_LABELS = self::KEY_LABELS;
        $DICTIONARY = self::DICTIONARY;
        $key_values_user = [];
        $key_values_admin = [];

        $users = $this->em->getRepository(User::class)->findByParams(["role" => "ROLE_USER"]) ;
        foreach($users as $user){
            $key_values_user[] = [
                "id" => $user->getId(),
                "lastname_firstname" => implode(" ", [$user->getLastname(), $user->getFirstname()]),
                "email" => $user->getEmail(),
                "phone" => $user->getPhone(),
                "is_active" => $user->isIsActive(),
            ];
        };
        
        $admins = $this->em->getRepository(User::class)->findByParams(["role" => "ROLE_ADMIN"]) ;
        foreach($admins as $admin){
            $key_values_admin[] = [
                "id" => $user->getId(),
                "lastname_firstname" => implode(" ", [$user->getLastname(), $user->getFirstname()]),
                "email" => $user->getEmail(),
                "phone" => $user->getPhone(),
                "is_active" => $user->isIsActive(),
            ];
        };

        // Onglet Liste des utilisateurs
        $formated = $this->exportExcel->formatExport(
            $onglet_name_user,
            $key_values_user, 
            $KEY_LABELS, 
            $DICTIONARY
        );
        $spreadsheet = $this->exportExcel->setupSpreadSheet($formated, [
            "superHeadSize" => 15,
            "superHeadColor" => "ffffff",
            "superHeadBgColor" => "ff0000",
            "headSize" => 10,
            "headColor" => "000000",
            "headBgColor" => "00ff00",
            "contentSize" => 10,
        ]);
        
        // Onglet Liste des administrateurs
        $formated = $this->exportExcel->formatExport(
            $onglet_name_admin,
            $key_values_admin, 
            $KEY_LABELS, 
            $DICTIONARY
        );
        $spreadsheet = $this->exportExcel->setupSpreadSheet($formated, [
            "superHeadSize" => 15,
            "superHeadColor" => "ff0000",
            "superHeadBgColor" => "00ff00",
            "headSize" => 10,
            "headColor" => "ffffff",
            "headBgColor" => "0000ff",
            "contentSize" => 10,
        ], $spreadsheet, 1);

        $excel = $this->exportExcel->buildExcel("liste-utilisateurs.xlsx", $spreadsheet);

        if(is_file($excel)){
            return $excel;
        }

        return null;
    }
}
```

### Export Pdf
#### Importation de la classe
```php
use Pw\Exports\ExportPdf
```

#### Configuration 
Modification du fichier services.yaml pour autoriser le autowire de l’apiDataTable autowire: true
```yaml
services:
    # ...
    Pw\Exports\ExportPdf:
        autowire: true
```

#### Exemple d’utilisation
1- Création d’un service d’export
exemple : UserExportPdfService.php
```php
<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

use Twig\Environment;
use Pw\Exports\ExportPdf;
use Doctrine\ORM\EntityManagerInterface;

class UserExportPdfService {

    const DICTIONARY = [
        "is_active" => [
            "1" => "Oui",
            "0" => "Non",
            true => "Oui",
            false => "Non",
            null => "Non",
        ],
    ];

    const KEY_LABELS = [

        [
            "key" => "INFORMATION_GROUP",
            "label" => "Information Utilisateur",
        ],
        [
            "key" => "id",
            "label" => "Id",
        ],
        [
            "key" => "lastname_firstname",
            "label" => "Nom et Prénom",
        ],
        [
            "key" => "email",
            "label" => "Email",
        ],
        [
            "key" => "phone",
            "label" => "Téléphone",
        ],
        [
            "key" => "is_active",
            "label" => "Validation",
        ],


        [
            "key" => "teams",
            "label" => "Equipes",
            "key_labels" => [
                [
                    "key" => "lastname_firstname",
                    "label" => "Nom et Prénom",
                ],
                [
                    "key" => "email",
                    "label" => "Email",
                ],
                [
                    "key" => "phone",
                    "label" => "Téléphone",
                ],
                [
                    "key" => "is_active",
                    "label" => "Validation",
                ],
            ]
        ],


        [
            "key" => "formations",
            "label" => "Formations",
            "model" => "MODEL_1"
        ],


        [
            "key" => "etudes",
            "label" => "Etudes",
            "model" => "MODEL_1"
        ],

    ];

    public function __construct(
        private EntityManagerInterface $em,
        private Environment $twig,
        private ExportPdf $exportPdf
    ){}

    /**
     * public methode to generate an excel file
     * 
     * @return Object || null
     */
    public function generatePdf(){

        $KEY_LABELS = self::KEY_LABELS;
        $DICTIONARY = self::DICTIONARY;

        $key_values = [

            "id" => "ed925dc-700f-6e6e-a29b-0b5a8a095f25",
            "lastname_firstname" => implode(" ", ["Doe", "Jane"]),
            "email" => "janeDoe@gmail.com",
            "phone" => "01 01 01 01 01",
            "is_active" => true,

            "teams" => [
                [
                    "lastname_firstname" => "Equipe1 Equipe1",
                    "email" => "Equipe1@gmail.com",
                    "phone" => "02 02 02 02 02",
                    "is_active" => true,
                ],
                [
                    "lastname_firstname" => "Equipe2 Equipe2",
                    "email" => "Equipe2@gmail.com",
                    "phone" => "03 03 03 03 03",
                    "is_active" => false,
                ],
            ],

            "etudes" => [
                [
                    "date_start" => "2005",
                    "date_end" => "2009",
                    "description" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. ",
                ],
                [
                    "date_start" => "2012",
                    "date_end" => "2015",
                    "description" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ",
                ],
            ],

            "formations" => [
                [
                    "date_start" => "2010",
                    "date_end" => "2012",
                    "description" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. ",
                ],
                [
                    "date_start" => "2012",
                    "date_end" => "2015",
                    "description" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ",
                ],
            ],

        ];

        $formated_datas = $this->exportPdf->formatExport(
            $key_values, 
            $KEY_LABELS,
            $DICTIONARY,
        );

        $template = "pdf/template.html.twig";

        $content_html = $this->twig->render($template , [
            "formated_datas" => $formated_datas,
        ]);

        $dompdf = $this->exportPdf->generateDomPdf($content_html);

        return $dompdf;

    }

}
```

2- Création du template
exemple : template\pdf\template.html.twig
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>PDF</title>
    <meta name="description" content="Notre évènement exceptionnel des 30 et 31 janvier prochain au Parc des expositions de Paris Nord Villepinte." />
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Poppins" />
    <style>
    @page {
        margin: 2cm 1.5cm;
        size: A4 landscape;
    }
    body {
        margin: 20px;
        font-family: 'Poppins';
    }
    </style>
</head>
<body>
    <div>
        {{ renderPdfContent(formated_datas)|raw }}
    </div>
</body>
</html>
```

3- Création de la fonction Twig renderPdfContent
Dans le fichier src\Twig\AppExtension.php
```php
<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Object\CustomTranslationObject;
use Twig\Environment;

class AppExtension extends AbstractExtension
{

    public function __construct(
        private Environment $twig
    ){
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('renderPdfContent', [$this, 'renderPdfContent']),
        ];
    }

    /**
     * 
     * @param [] $formated_datas
     * @return string
     */
    function renderPdfContent($formated_datas)
    {
        $template = "pdf/content.html.twig";

        $content_html = $this->twig->render($template , [
            "formated_datas" => $formated_datas,
        ]);

        return $content_html ;
    }
}
```

4- Création du contenu html
exemple : templates\pdf\content.html.twig
```html
{% if formated_datas is defined and formated_datas %}

    {% for data_index, data in formated_datas %}

        {% if data.key and data.key is same as("INFORMATION_GROUP") %}
            <h2>{{data.label | raw}}</h2>
            

        {% elseif data.key and data.key is same as("teams") %}
            <h2>{{data.label | raw}}</h2>

            {% for equipe_index, equipe in data.value %}
                <fieldset>
                    <legend>
                        Equipe {{ equipe_index + 1 }}
                    </legend>
                    {{ renderPdfContent(equipe)|raw }}
                </fieldset>
            {% endfor %}


        {% elseif data.model and data.model is same as("MODEL_1") %}
            <h2>{{data.label | raw}}</h2>

            {% for element_index, element in data.value %}
                <fieldset>
                    <legend>
                        {{ element.date_start }} - {{ element.date_end }}
                    </legend>
                    <p>{{ element.description }}</p>
                </fieldset>
            {% endfor %}


        {% else %}
            <strong>{{data.label | raw }} : </strong>
            <span>{{data.value}}<span/> <br/>

        {% endif %}

    {% endfor %}

{% endif %}
```

5- Création du contrôleur
exemple : ExportController.php
```php
<?php

namespace App\Controller;

use App\Service\UserExportPdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends AbstractController
{
    #[Route('/user/export/pdf', name: 'user_export_pdf')]
    public function userExportPdfAction(
        UserExportPdfService $userExportPdfService
    ): Response
    {
        $dompdf = $userExportPdfService->generatePdf();

        $filename = "information-utilisateur-".date("d-m-Y-").uniqid();
        $dompdf->stream($filename, [
            "Attachment" => true
        ]);

        return new Response('', 200, [
          'Content-Type' => 'application/pdf',
        ]);
    }
}
```

### Generator

#### Configuration 
Modification du fichier services.yaml pour autoriser le autowire de Pw\Command\GeneratorCommand autowire: true
```yaml
    Pw\Command\GeneratorCommand:
        autowire: true
        # redundant thanks to _defaults, but value is overridable on each service
```

#### Exemple d'utilisation 

###### Pour créer une page front
```php bin/console pw-generator:generate page front```
###### Pour créer un API front
```php bin/console pw-generator:generate api front```
###### Pour créer un service front
```php bin/console pw-generator:generate service front```

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

### FileValidator
#### Importation de la classe
```php
use Pw\FileValidator\FileValidator
```

#### Instanciation
```php
$fileValidator = new FileValidator([]);
```

#### Exemple d’utilisation
###### Utilisation avec les définitions des extensions, des types et de la taille des fichiers autorisés
```php
use Pw\FileValidator\FileValidator

$allowedMimeTypes = ['image/jpeg', 'image/png'];
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$maxFileSize = 1024 * 1024; // 1 MB

$fileValidator = new FileValidator(
    $allowedMimeTypes, 
    $allowedExtensions, 
    $maxFileSize
);

$file = [
    'name' => 'example.jpg',
    'type' => 'image/jpeg',
    'size' => 500000, // 500 KB
];

if ($fileValidator->validateFile($file)) {
    //Le fichier est valide.
} else {
    //Le fichier n'est pas valide.
}
```
###### Utilisation de verifier le type de fichier comme `image` en appelant juste la méthode `isImage`.
```php
use Pw\FileValidator\FileValidator

$fileValidator = new FileValidator();

$mimetype = $file->getMimeType();//Type de fichier

if ($fileValidator->isImage($mimetype)) {
    //Le fichier est un image.
} else {
    //Le fichier n'est un image.
}

//ou
if ($fileValidator->isImage($file)) {//Objet de fichier
    //Le fichier est un image.
} else {
    //Le fichier n'est un image.
}
```

###### Quelques méthodes sur la validation de fichier.
```php
uploadToPath($file, $dir, $file_name)
isImage($file)
isPdf($file)
isDocx($file)
isExcel($file)
isVideo($file)
getFileType($file)
validSize($file, $max_mb=1)
validateMimeType($file)
validateExtension($file)
validateFile($file)
```