<?php

    require_once("bases/Controller.php");
    require_once("utils/Upload.php");
    require_once("models/Users.php");
    require_once("models/Newsletter.php");
    require_once("models/Categories.php");
    require_once("models/Types.php");
    require_once("models/Comments.php");
    require_once("models/Meals.php");
    require_once("models/Meals_Categories.php");

    class SiteController extends Controller
    {
        /********** View Display **********/
        /**
         * Display home page
         * 
         * @return void
         */
        public function displayHomepage()
        {
            $this->setSessionPages("index");
            $title = "Homepage";
            /* create a json file with all comments to manipulate with JS */
            $comments = json_encode((new Comments)->all());
            $file = fopen('utils/comments.json', 'w');
            fwrite($file, $comments);
            fclose($file);

            include("views/homepage.view.php");
        }

        /**
         * Display menu page
         *
         * @return void
         */
        public function displayMenu()
        {
            $this->setSessionPages("menu");

            $title = 'Menu';
            
            /* create json files to display menu with vueJS */
            $types = (new Types)->all();
            $types_json = json_encode($types);
            $file = fopen('utils/types.json', 'w');
            fwrite($file, $types_json);
            fclose($file);

            $categories = json_encode((new Categories)->all());
            $file = fopen('utils/categories.json', 'w');
            fwrite($file, $categories);
            fclose($file);

            $meals = (new Meals)->getAllMealsAndCategories();
            // if there is no meal, meals is an empty array
            if(!$meals) $meals = false;
            $meals = json_encode($meals);
            $file = fopen('utils/meals.json', 'w');
            fwrite($file, $meals);
            fclose($file);

            include("views/menu.view.php");
        }

        /**
         * Display about us page
         *
         * @return void
         */
        public function displayAboutUs()
        {
            $this->setSessionPages("a-propos");
            $title = "À Propos de PUB G4";
            include("views/aboutus.view.php");
        }

        /**
         * Display contact page
         *
         * @return void
         */
        public function displayContact()
        {
            $this->setSessionPages("contact");
            $title = "Contact";
            include("views/contact.view.php");
        }

        /**
         * Detrmine if the restaurant is open or not
         *
         * @return boolean true if open, false otherwise
         */
        public function isOpen()
        {
            date_default_timezone_set('America/New_York');
            if(date('D') == "Sat" || date('D') == "Sun")
            {
                if(date('H') < 2 || date('H') > 11) return true;
            }
            else
            {
                if(date('H') < 1 || date('H') > 11) return true;
            }
            return false;
        }
        
        /********** Form Display **********/
        /**
         * Display forms' page with specified form
         *
         * @param string $title
         * @param string $display name of the specified form
         * @return void
         */
        public function displayFormPage(string $title, string $display)
        {
            switch($display)
            {
                case 'infolettre': $form_title = "S'inscrire à l'infolettre"; break;
                case 'connexion': $form_title = "Connexion"; break;
                case 'compte': $form_title = "Créer un compte"; break;
                case 'modifier-plat': $form_title = "Modification d'un plat"; break;
                case 'ajouter-plat': $form_title = "Ajouter un plat"; break;
                case 'categorie': $form_title = "Modification de catégories"; break;
            }
            include("views/form.view.php");
        }
        
        /**
         * Get informations in database and display the specified form
         *
         * @param string $display name of the form to display
         * @return void
         */
        public function displayForm(string $display)
        {
            // not enough datas to do queries only when it's used
            $categories["categories"] = (new Categories)->all();
            $categories["types"] = (new Types)->all();
            $meal = $this->getMealByID();

            switch($display)
            {
                case "infolettre": include("views/parts/newsletter.form.php"); break;
                case "connexion": include("views/parts/connection.form.php"); break;
                case "compte": include("views/parts/createaccount.form.php"); break;
                case "modifier-plat":
                    if(isset($meal)) include("views/parts/modify_meal.form.php");
                    break;
                case "ajouter-plat": include("views/parts/add_meal.form.php"); break;
                case "categorie": include("views/parts/categories.form.php"); break;
                default: Errors::errorSwitch(6);
            }
        }

        /**
         * Get meal informations of the id received with GET
         * 
         * @return array|false associative array of false if there is no id in GET
         */
        public function getMealByID()
        {
            if(!isset($_GET["id"]) || empty($_GET["id"])) return false;
            $meal = (new Meals)->byId($_GET["id"]);
            if(!$meal) $this->redirect("menu?error=12");
            $categories_for_meal = (new Categories)->getMealCategories($meal["id"]);
            $meal["categories"] = $this->betterDisplay($categories_for_meal);
            return $meal;
        }

        /**
         * Get informations to display the requested form
         * 
         * @return void
         */
        public function displayNewsletterForm()
        {
            $this->setSessionPages("infolettre");
            $title = "S'inscrire à l'infolettre";
            $display = "infolettre";
            $this->displayFormPage($title, $display);
        }
        
        /**
         * Get informations to display the requested form
         * 
         * @return void
         */
        public function displayLogIn()
        {
            if($this->verifyUser()) $this->redirect("menu?error=11");
            $this->setSessionPages("connexion");
            $title = "Connexion";
            $display = "connexion";
            $this->displayFormPage($title, $display);
        }
        
        /**
         * Get informations to display the requested form
         * 
         * @return void
         */
        public function displayAccountCreation()
        {
            if(!$this->verifyAdmin()) $this->redirect("index");
            $this->setSessionPages("creer-compte");
            $title = "Créer un compte";
            $display = "compte";
            $this->displayFormPage($title, $display);
        }

        /**
         * Get informations to display the requested form
         * 
         * @return void
         */
        public function displayUpdateMeal()
        {
            if(!$this->verifyUser()) $this->redirect("index");
            $this->setSessionPages("modifier-plat");
            $title = "Modification d'un plat";
            $display = "modifier-plat";
            $this->displayFormPage($title, $display);
        }

        /**
         * Get informations to display the requested form
         *
         * @return void
         */
        public function displayAddMeal()
        {
            if(!$this->verifyUser()) $this->redirect("index");
            $this->setSessionPages("ajouter-plat");
            $title = "Ajout de plat";
            $display = "ajouter-plat";
            $this->displayFormPage($title, $display);
        }

        /**
         * Get informations to display the requested form
         * 
         * @return void
         */
        public function displayUpdateCategories()
        {
            if(!$this->verifyUser()) $this->redirect("index");
            $this->setSessionPages("modifier-categorie");
            $title = "Modification des catégories";
            $display = "categorie";
            $this->displayFormPage($title, $display);
        }

        /********** Submit Form **********/
        /***** Users *****/
        /**
         * Process informations to create an account
         *
         * @return void
         */
        public function createAccount()
        {
            $this->verifyPOST("creer-compte", "creer-compte?error=1");

            $email = $_POST["email"];
            $users = new Users();
            
            if(!$users->verifyUniqueEmail($email)) $this->redirect("creer-compte?error=3");

            $first_name = $_POST["first_name"];
            $last_name = $_POST["last_name"];
            $password = $_POST["password"];

            $success = $users->create($first_name, $last_name, $email, $password);

            if($success) $this->redirect("menu?success=2");
            $this->redirect("creer-compte?error=2");
        }

        /**
         * Submit log in with email and password
         *
         * @return void
         */
        public function logIn()
        {
            $this->verifyPOST("connexion", "connexion?error=1");

            $email = $_POST["email"];
            $password = $_POST["password"];
            $success = (new Users)->log($email, $password);

            if(!$success) $this->redirect("connexion?error=4");
            $this->redirect("menu");
        }

        /**
         * Log out the user
         *
         * @return void
         */
        public function logOut()
        {
            $_SESSION["user_id"] = 0;
            if(isset($_SESSION["admin"])) $_SESSION["admin"] = false;
            $this->redirect("index");
        }

        /**
         * Verify if the user is a connected user
         *
         * @return bool true if the user is connected, false otherwise
         */
        public function verifyUser() : bool
        {
            if(!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == 0) return false;
            return true;
        }

        /**
         * Verify is the user is an admin
         *
         * @return bool true if the user is an admin, false otherwise
         */
        public function verifyAdmin() : bool
        {
            if(!$this->verifyUser()) return false;
            if(!isset($_SESSION["admin"]) || $_SESSION["admin"] != true) return false;
            return true;
        }
        
        /***** Newsletter *****/
        /**
         * Process users email and name to add in Newsletter_info database
         *
         * @return void
         */
        public function subscribeNewsletter()
        {
            $this->verifyPOST("infolettre", "infolettre?error=1");

            $email = $_POST["email"];
            $newsletter = new Newsletter();

            if(!$newsletter->verifyUniqueEmail($email)) $this->redirect("infolettre?error=3");

            $success = $newsletter->subscribe($email, $_POST["name"]);

            if(!$success) $this->redirect("infolettre?error=5");
            $this->redirect("menu?success=1");
        }

        /***** Categories *****/
        /**
         * Process new categories and meal type to add into the databse
         *
         * @return void
         */
        public function addCategory()
        {
            if(empty($_POST)) $this->redirect("modifier-categories");
            if(empty($_POST["category"]) && empty($_POST["type"])) $this->redirect("modifier-categories?error=1");

            $success = false;
            if(!empty($_POST["category"])) $success = (new Categories)->insert($_POST["category"]);
            if(!empty($_POST["type"])) $success = (new Types)->insert($_POST["type"]);

            if($success) $this->redirect("menu?success=2");
            $this->redirect("modifier-menu?error=9");
        }
        
        /**
         * Process modifications on a specifif category
         *
         * @return void
         */
        public function modifyCategory()
        {
            $this->verifyPOST("modifier-categories", "modifier-categories?error=1");
            $type = $_POST["type"];
            $id = $_POST["id"];
            $name = $_POST["name"];

            if($type == "type") $success = (new Types)->edit($id, $name);
            else $success = (new Categories)->edit($id, $name);

            if($success) $this->redirect("menu?success=3");
            $this->redirect("modifier-categories?error=8");
        }
        
        /**
         * Process delete query of a category or type of meal
         *
         * @return void
         */
        public function deleteCategory()
        {
            $this->verifyPOST("modifier-categories", "modifier-categories?error=1");
            
            $type = $_POST["type"];
            $id = $_POST["id"];

            if($type == "type") $success = (new Types)->delete($id);
            else $success = (new Categories)->delete($id);

            if($success) $this->redirect("menu?success=4");
            $this->redirect("modifier-categories?error=8");
        }

        /***** Meals *****/
        /**
         * Process new meal to add it in the database
         *
         * @return void
         */
        public function addMeal()
        {
            $this->verifyPOST("ajouter-plat", "ajouter-plat?error=1");
            if($_POST["category"] == 0 || $_POST["type"] == 0) $this->redirect("ajouter-plat?error=1");

            $upload = new Upload("image", ["jpg", "jpeg", "png", "webp"]);
            if(!$upload->isValid()) $this->redirect("ajouter-plat?error=10");
            $image_path = $upload->moveTo("public/uploads");

            $success = (new Meals)->createMeal($_POST["name"], $_POST["type"], $_POST["category"], $_POST["description"], $_POST["price"], $image_path);

            if($success) $this->redirect("menu?success=5");
            $this->redirect("ajouter-plat?error=9");
        }
        
        /**
         * Process delete query of a meal
         *
         * @return void
         */
        public function deleteMeal()
        {
            $this->verifyPOST("modifier-plat", "modifier-plat?error=1");

            $success = (new Meals)->delete($_POST["id"]);

            if($success) $this->redirect("menu?success=6");
            $this->redirect("modifier-plat?error=9");
        }
        
        /**
         * Process modifications query of a meal
         * 
         * @return void
         */
        public function modifyMeal()
        {
            if(empty($_POST)) $this->redirect("modifier-plat");
            if(empty($_POST["name"]) ||empty($_POST["description"]) ||empty($_POST["type"]) ||empty($_POST["category"]) ||empty($_POST["price"]) ||empty($_POST["id"]))  $this->redirect("modifier-plat?error=1");

            $upload = new Upload("image", ["jpg", "jpeg", "png", "webp"]);
            if($upload->isValid())
            {
                $image_path = $upload->moveTo("public/uploads");

                $success = (new Meals)->modifyMealPicture($image_path, $_POST["id"]);
                if(!$success) $this->redirect("modifier-plat?error=9");
            }

            $success = (new Meals)->modifyMeal($_POST["name"], $_POST["description"], $_POST["type"], $_POST["category"], $_POST["price"], $_POST["id"]);

            if(!$success) $this->redirect("modifier-plat?error=9");
            $this->redirect("menu?success=7");
        }

        /***** Meals and Categories *****/
        /**
         * Process addition of a new category to a meal
         *
         * @return void
         */
        public function addNewCategory()
        {
            $this->verifyPOST("modifier-categories", "modifier-categories?error=1");

            $success = (new Meals_Categories)->addNewCategory($_POST["category"], $_POST["meal"]);
            if($success) $this->redirect("menu?success=8");
            $this->redirect("modifier-menu?error=9");
        }

        /**
         * Process delete query of a category for a specified meal
         *
         * @return void
         */
        public function deleteCategoryOfMeal()
        {
            $this->verifyPOST("modifier-menu", "modifier-menu?error=1");

            $success = (new Meals_Categories)->deleteCategoryOfMeal($_POST["category_name"], $_POST["meal_id"]);
            if($success) $this->redirect("menu?success=9");
            $this->redirect("modifier-menu?error=9");
        }

        /***** General Methods *****/

        /**
         * better display for categories array in each meal
         * 
         * @param array|bool $array, false if array is empty
         */
        public function betterDisplay(array $array)
        {
            if(empty($array)) return false;

            foreach($array as $item)
            {
                $result[] = $item["name"];
            }
            return $result;
        }

        /**
         * Get image source of the image to use as background-image
         *
         * @param string $display form display
         * @return string image's source
         */
        public function getBGimage($display)
        {
            if($display != 'modifier-plat') return "background-image: url('public/images/filets_de_poulet.jpg')";
            return "background-image: url('" . $this->getMealByID()["image"] . "')";
        }
    }

?>