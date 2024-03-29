<div class="modify" v-show="affichage == 'modify'">
    <div class="modify-meal-form">
        <!-- modify meal -->
        <form action="modify_meal_submit" method="post" enctype="multipart/form-data">
            <div class="input-infos">
                <div class="input">
                    <input type="text" name="name" value="<?= $meal['name'] ?>" required>
                    <span>Nom du plat :</span>
                </div>
                <div class="input area">
                    <textarea name="description" cols="30" rows="10" value="<?= $meal['description'] ?>" required></textarea>
                    <span>Description :</span>
                </div>
                <div class="selects">
                    <div class="input">
                        <select name="type">
                            <?php
                                foreach($categories["types"] as $type)
                                {
                                    ?>
                                        <option value="<?= $type['id'] ?>" <?= (new Meals)->isTypeSelected($meal["id"], $type["id"]) ?>><?= $type["name"] ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                        <span>Type :</span>
                    </div>
                    <div class="input">
                        <?php
                        if($meal["categories"])
                        {
                            foreach($meal["categories"] as $category_of_meal)
                            {
                                ?>
                                <select name="category">
                                    <?php
                                        foreach($categories["categories"] as $category)
                                        {
                                            ?>
                                                <option value="<?= $category['id'] ?>" <?= (new Meals)->isCategorySelected($category_of_meal, $category["name"]) ?>><?= $category["name"] ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>
                                <span>Catégorie :</span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="input">
                        <input type="number" name="price" value="<?= $meal['price'] ?>" step=".01" required>
                        <span>Prix :</span>
                    </div>
                </div>
                <div class="input">
                    <input type="file" name="image" class="file" id="file">
                    <label for="file">Choisir un Fichier</label>
                    <span>Image :</span>
                </div>
            </div>
            <input type="hidden" name="id" value="<?= $meal['id'] ?>">
            <input type="submit" value="Soumettre" class="submit modify-meal">
        </form>
    </div>
</div>

<div id="modify-category-of-meal" v-show="affichage == 'add'">
        <!-- add a category to the actual meal -->
        <form action="add_category_submit" method="post">
            <div class="selects">
                <div class="input">
                    <select name="category">
                        <?php
                            foreach($categories["categories"] as $category)
                            {
                                ?>
                                    <option value="<?= $category['id'] ?>" <?= (new Meals_Categories)->isCategoryAssociated($meal["id"], $category["id"])?>><?= $category["name"] ?></option>
                                <?php
                            }
                        ?>
                    </select>
                    <span>Ajouter une catégorie :</span>
                </div>
            </div>
            <input type="hidden" name="meal" value="<?= $meal['id'] ?>">
            <input type="submit" value="Soumettre" class="submit">
        </form>

        <hr>
    <?php
        foreach($meal["categories"] as $category)
        {
            ?>
                <div class="infos">
                    <p><?= $category ?></p>
                    <form action="delete_category_of_meal_submit" method="post" class='delete'>
                        <div class="input-infos">
                            <input type="hidden" name="category_name" value="<?= $category_of_meal ?>">
                            <input type="hidden" name="meal_id" value="<?= $meal['id'] ?>">
                            <input type="submit" value="🗑" class='submit'>
                        </div>
                    </form>
                </div>
            <?php
        }
    ?>
</div>

<div class="modify" v-show="affichage == 'added'">
</div>