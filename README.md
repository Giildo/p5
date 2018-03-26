# Initialisation du projet

## Partie publique

Création d'un dossier "`public`" à la racine qui regroupera tous les fichiers accessibles au visiteur (css, js, index.php...). Il ne contiendra aucune logique.  
Création du fichier "index.php" à la racine du dossier "`public`". C'est vers ce fichier que toutes les adresses pointeront. Il sera nécessaire de faire .htaccess qui pointera vers ce dernier. Il est chargé de créer le Container et l'Application et de les lancer.  
Créer un dossier "css", "js" et "img" dans ce dossier pour y stocker les fichiers correspondants.

## Le Container

Utilisation de [PHP-DI](http://php-di.org/) comme Container.
Il est nécessaire de l'initialiser avec les différents paramètres de l'application :  
`$builder = new ContainerBuilder();`  
`$builder->addDefinitions("pathOfConfigFiles");`  
`$container = $builder->build();`  
  
Il est conseillé de séparer les logiques du code, et donc les différents types de configuration. Exemple de séparation possible :  
* Config générale du site (nombre d'article par page, les options pour la connexion à la BDD...)  
* Config des classes (initialisation des différentes classes avec leurs constructeurs)  
* Config du lien entre les modèles et les contrôleurs.  

Le seul fichier obligatoire est "orm_config.php" qui regroupe l'appel aux entités, nécessaire pour le fonctionnement de l'ORM.  
Pour plus d'information se reporter à la partie configuration.  

# Configuration

Les fichiers de configuration peuvent être placés où vous le souhaitez. Il seront retrouvés grâce à l'appel lors de l'initialisation du Container (cf. ci-dessus). Création de trois fichiers de config dans `/src/config/` dans  :  
* `config.php`  
* `configClasses.php`  
* `configController.php`  
  
Ils doivent comporter un certains nombres d'informations obligatoires :  

## PDO

Pour le bon fonctionnement du Framework, il est nécessaire d'initialiser PDO dans le Container. Pour ce faire, dans le fichier `config.php` :  
`'db.name'     => 'nomDeLaBDD',`  
`'db.user'     => 'nomDUtilisateurPourSeConnecter',`  
`'db.password' => 'MDP',`  
`'db.host'     => 'nomDuServeur'`  
`'pdo.options' => [`  
&nbsp;&nbsp;&nbsp;&nbsp;`PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,`  
&nbsp;&nbsp;&nbsp;&nbsp;`PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION`  
`    ]`  
...dans le fichier `configClasses.php` :  
`PDO::class => function (ContainerInterface $c) {`  
&nbsp;&nbsp;&nbsp;&nbsp;`return new PDO(`  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`'mysql:host=' . $c->get('db.host') . ';dbname=' . $c->get('db.name') . ';charset=utf8',`  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$c->get('db.user'),`  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$c->get('db.password'),`  
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$c->get('pdo.options')`  
&nbsp;&nbsp;&nbsp;&nbsp;`);`  
`}`  

## Configuration de Twig

Dans le fichier "`config.php`" :  
`'twig.options' => [],`  
`'twig.views'   => dirname(__DIR__, 2) . '/views'`  
Le tableau est l'ensemble des options qu'on passe à Twig lors de son initialisation (cf. [Twig for Developers](https://twig.symfony.com/doc/2.x/api.html)), il permettra par exemple de définir un fichier de cache pour les vues.  
Le second paramètre est le dossier où se retrouveront toutes les vues. C'est un chemin relatif par rapport au dossier où se trouve le fichier de config et c'est par rapport à ce dossier qu'on appellera les vues dans les fonctions "`render`" des contrôleurs.  
  
Dans le fichier "`configClasses.php`" :  
`Twig_Loader_Filesystem::class => object()->constructor(get('twig.views')),`  
`Twig_Environment::class       => object()->constructor(`  
&nbsp;&nbsp;&nbsp;&nbsp;`get(Twig_Loader_Filesystem::class),`  
&nbsp;&nbsp;&nbsp;&nbsp;`get('twig.options')`  
`)`  

## Configuration de l'application

Dans le fichier "`config.php`" :  
`'app.prefix' => '\App',`  
`'app.routes' => __DIR__ . '/routes.xml'`  
La première option correspond au préfixe qu'on appliquera à Namespace de notre application. Ce Namespace se décomposera en : "prefixe de l'application" + "Nom du module appelé" + "Controller" + "Nom du Controller". Ici par exemple mon "`PostController`" sera dans le namespace : "`App\Blog\Controller\PostController;`".  
La seconde option correspond au chemin pour appeler le fichier des routes (plus d'information ci-dessous).
  
Dans le fichier "`configClasses.php`" on initialise le routeur :  
`Router::class => object()->constructor(`  
&nbsp;&nbsp;&nbsp;&nbsp;`get('app.prefix'),`  
&nbsp;&nbsp;&nbsp;&nbsp;`get('app.routes'),`  
&nbsp;&nbsp;&nbsp;&nbsp;`get(ContainerInterface::class)`  
`)`  

# Application

Une fois la configuration terminée, on crée les premiers blocs de l'application.  

## Base de l'application

Dans le dossier "`src`", le premier élément obligatoire est l'"`AppController`". Il est une jointure entre le contrôleur du Framework et l'application. Il permettra à l'utilisateur du Framework de créer des méthodes personnalisées.  
Il doit se placer dans "`src/App/Controller/AppController.php`". Il doit étendre de "`Jojotique\Framework\Controller\Controller`". Tous les contrôleurs de l'application, étendront de ce "`AppController`".

## Les routes

Le framework est crée pour faire de l'URL Rewriting : toutes les adresses pointent vers le même fichier et c'est le routeur qui définie la route.  
Les routes se créent dans le fichier "`routes.xml`". Elles sont écritent en XML.

### Bases du dossier routes.xml

`<?xml version="1.0" encoding="UTF-8" ?>`  
`<routes>`  
&nbsp;&nbsp;&nbsp;&nbsp;`<route name="" path="" controller="" method="" />`  
`</routes>`  

### Exemples de route

#### Route sans option :  
`<route name="general_accueil" path="^/accueil" controller="general" method="index" />`  
* Name : nom de la route, permet de ne pas avoir de doublon et défini le dossier d'appel du contrôleur. Il se définie en mettant "nom du module" + _facultatif si plusieurs contrôleurs dans le module_ "nom du contrôleur" + "nom de la méthode".
* Path : adresse qui permettra de sélectionner cette route;
* Controller : contrôleur qui sera appelé, ici 'GeneralController.php';
* Method : méthode qui sera appelée dans le contrôleur.  
  
Ici le routeur appelera la méthode "`index`" dans le contrôleur "`App\General\Controller\GeneralController`" : 
* "App" : définit dans les options;
* "General" : définie dans le nom de la route;
* "Controller" : architecture par défaut dans ce Framework;
* "GeneralController" : définie dans le paramètre "controller" de la route + "Controller" par défaut dans ce Framework.
  
  
#### Route avec option :  
`<route name="blog_post_category" path="^/categorie/{slug: \w+}-{id: \d+}" controller="post" method="category" />`  
Pour "name", "controller" et "method" tout reste identique que pour les routes sans options.
La différence se trouve dans "path". Ici on retrouve deux options qui seront récupérées par le routeur :
* {slug: \w+} : récupère le "slug" de la catégorie. Il est composé de caractères alphanumériques, définie par "\w+";
* {id: \d+} : récupère l'"id". Il est composé uniquement de chiffres, définie par "\d+".  
  
Pour en savoir plus sur les options des [regex](https://openclassrooms.com/courses/concevez-votre-site-web-avec-php-et-mysql/les-expressions-regulieres-partie-1-2). Ces options se récupère au niveau du paramètre de la méthode appelée dans "method".  
Ex : si on appelle ici la méthode "`category`" de "`App\Blog\Controller\PostController`", pour récupérer le slug on fera :  
`public function category(array $vars)`  
`{`  
&nbsp;&nbsp;&nbsp;&nbsp;`$slug = $vars['slug'];`  
`}`  

## Le contrôleur

Tous les contrôleurs doivent étendre de "`AppController`". Il devra contenir, au moins, une méthode par route disponible (cf. ci-dessus).  
  
Si le contrôleur doit récupérer des informations au niveau de la base de données, il aura besoin de modèles qui se chargeront de le faire. La définition des modèles nécessaire est automatique lors de la création des contrôleurs par l'application.

### Contrôleur et modèle

Pour que le contrôleur est la possibilité de faire appel à un modèle, il faut le définir dans les options. Dans le fichier "`configController.php`" :  
`'blog.post.models' => [`  
&nbsp;&nbsp;&nbsp;&nbsp;`'post'     => get(PostModel::class),`  
&nbsp;&nbsp;&nbsp;&nbsp;`'category' => get(CategoryModel::class),`  
&nbsp;&nbsp;&nbsp;&nbsp;`'user'     => get(UserModel::class)`  
`],`  
Cette définition permettra au "`App\Blog\Controller\PostController`" de faire appel à :
* PostModel;
* CategoryModel;
* UserModel

### Contrôleur et vue

Pour faire appel à la vue, le contrôleur a par défaut la méthode "`render`" :  
`$this->render('chemin de le vue', 'variables');`  
Le chemin est relatif au dossier définie en option dans Twig.  
Les variables doivent être passées sous forme de tableau associatif. Ex. : `compact('form', 'posts', 'categories')`

## l'ORM

Pour récupérer des informations dans la BDD, ce Framework est équipé d'un ORM qui est là pour faire la jointure entre les contrôleurs, les modèles et les entités. Il servira à récupére de manière sécurisée les informations en BDD. De plus, il permet de manipuler la BDD comme si c'était un objet.  

### ORM et modèle

Lorsqu'on crée un modèle, il faut le faire étendre de "`Jojotique\Framework\Model\Model`". Il est également nécessaire d'implémenter la "`Jojotique\ORM\Interfaces\ORMModelInterface`".  
La seule information indispensable au niveau du modèle, c'est le nom de la table avec laquelle elle va travailler :  
Ex. : `protected $table = 'gender';`  
  
### ORM et entité

Lorsqu'on crée une entité, il faut la faire étendre de "`Jojotique\Framework\Entity\Entity`". Il est également nécessaire d'implémenter la "`Jojotique\Framework\Entity\EntityInterface`".
L'entité doit avoir comme nom celui de la tablle qu'elle représente :  
Ex. : "`protected $tableName = 'gender';`"  
  
Puis on définie en paramètre les colonnes de la table et on crée leurs getters et setters.

### ORM et contrôleur

#### Récupération d'entité en BDD

Pour ce faire on utilise l'objet "`select`" qui se trouve par défaut dans tous les contrôleurs. Cet objet possède plusieurs méthodes qui réprésente les différentes parties d'un "`SELECT`" en SQL.  
Ex. :  
`$gender = $this->select->select(['gender' => ['id', 'gender']])`  
&nbsp;&nbsp;&nbsp;&nbsp;`->from('gender')`  
&nbsp;&nbsp;&nbsp;&nbsp;`->where(['gender' => 'homme'])`  
&nbsp;&nbsp;&nbsp;&nbsp;`->singleItem()`  
&nbsp;&nbsp;&nbsp;&nbsp;`->execute($this->genderModel);`

#### Sauvegarde d'une entité en BDD

On utilise la méthode "`save`" de l'objet "`select`".  
Ex. : `$this->select->save($gender, $this->genderModel);`  
  
L'ORMSelect fait la différence entre une entité qui existe déjà et une nouvelle entité. Si c'est le premier cas il fera un "`UPDATE`" dans la BDD, sinon il fera un "`INSERT`".

#### Suppression d'une entité en BDD

On utilise la méthode "`delete`" de l'objet "`select`".  
Ex. : `$this->select->delete($gender, $this->genderModel);`


#### Pour aller plus loin

Pour plus d'information, se référer à la [documentation](https://github.com/Giildo/jjtq-orm) de l'ORM.

# Lancement de l'application

Une fois tout cela fait, dans le fichier "`index.php`", initialiser l'application grâce au container :  
`$app = $container->get(App::class);`  
  
Puis lancer l'application :  
`$app->run();`  
