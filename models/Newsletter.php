<?php
    
    require_once("bases/Model.php");

    class Newsletter extends Model
    {
        // Model has to specify his table
        protected $table = "newsletter_infos";  
        
        /**
         * Add informations in Newsletter_infos to subscrire to the newsletter
         *
         * @param string $email
         * @param string $name
         * @return bool true if successful, false otherwise
         */
        public function subscribe(string $email, string $name) : bool
        {
            $sql = "INSERT INTO $this->table (email, name)
                    VALUES (:email, :name)";
    
            $stmt = $this->pdo()->prepare($sql);
    
            return $stmt->execute([
                ":email" => $email,
                ":name" => $name
            ]);
        }
    }
?>