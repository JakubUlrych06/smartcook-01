<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcook - Recepty</title>
    <link rel="icon" href="1046500.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <main>
    <?php
require_once("SmartCookClient.php");

$recipes = []; 

$filter_options = [
    "dish_category" => ["1" => "Breakfast", "2" => "Soup", "3" => "Main course", "4" => "Dessert", "5" => "Dinner"],
    "recipe_category" => ["1" => "Soup", "2" => "Meat", "3" => "Meat free", "4" => "Dessert", "5" => "Sauce", "6" => "Pasta", "7" => "Salad", "8" => "Sweet food", "9" => "Drink"],
    "difficulty" => ["1" => "Simple", "2" => "Medium", "3" => "Difficult"],
    "price" => ["1" => "Cheap", "2" => "Medium", "3" => "Expensive"],
    "tolerance" => ["1" => "Vegetarian", "2" => "Vegan", "3" => "Nuts", "4" => "Gluten", "5" => "Lactose", "6" => "Spicy", "7" => "Alcohol", "8" => "Sea food", "9" => "Mushrooms"]
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_options = [];
    foreach ($filter_options as $filter_name => $options) {
        if (isset($_POST[$filter_name])) {
            $selected_options[$filter_name] = $_POST[$filter_name];
        }
    }

    $request_data = [
        "attributes" => ["id", "name", "author", "difficulty", "price", "description"], 
        "filter" => $selected_options
    ];

    try {
        $response = (new SmartCookClient)
            ->setRequestData($request_data)
            ->sendRequest("recipes")
            ->getResponseData(); 


        $recipes = $response['data'];
    } catch (Exception $e) {
        echo "Error fetching recipes: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartcook</title>
    <style>
        .container {
            margin-top: -230px;
            margin-bottom: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #smartcook {
            font-family: 'Pacifico', cursive;
            color: #ffffff;
            text-shadow: 8px 0 0 #000000, 0 8px 0 #000000, -8px 0 0 #000000, 0 -8px 0 #000000;
            font-size: 110px;
            text-align: center;
            margin-top: -100px;
        }
        form {
            text-align: center;
            margin-top: 20px;
        }
        fieldset {
            border: 2px solid #000000; 
            margin: 10px;
            padding: 10px;
        }
        input[type="checkbox"] {
            display: none; 
        }
        label {
            display: inline-block; 
            margin-right: 10px; 
            cursor: pointer;
            background-color: white;
            border-radius: 3px;
            padding: 5px;
            transition: background-color 0.6s ease;
        }
        label:hover {
            background-color: #ffe2cf; 
        }
        .checked {
            background-color: grey !important; 
        }
        
    </style>
    <script>
        function updateFilters() {
            document.getElementById('filterForm').submit();
        }

        function toggleCheckedClass(checkbox) {
            var label = document.querySelector('label[for="' + checkbox.id + '"]');
            if (label) {
                if (checkbox.checked) {
                    label.classList.add('checked');
                } else {
                    label.classList.remove('checked');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function(checkbox) {
                toggleCheckedClass(checkbox);

                checkbox.addEventListener('change', function() {
                    toggleCheckedClass(checkbox);
                    updateFilters();
                });
            });

            var modal = document.getElementById('recipeModal');
            var span = document.getElementsByClassName('close')[0];

            document.querySelectorAll('.square-button').forEach(function(button) {
                button.onclick = function() {
                    var recipeId = this.getAttribute('data-recipe-id');
                    document.getElementById('recipeDetails').innerHTML = 'Recipe details for ID: ' + recipeId;
                    modal.style.display = 'block';
                };
            });

            span.onclick = function() {
                modal.style.display = 'none';
            };

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };
        });
    </script>
</head>
<body>
<h5 id="smartcook">Smartcook</h5>

<div class="container">
    <form id="filterForm" method="POST">
        <?php foreach ($filter_options as $filter_name => $options) : ?>
            <fieldset>
                <legend><?= ucfirst(str_replace("_", " ", $filter_name)) ?></legend>
                <?php foreach ($options as $value => $label) : ?>
                    <input type="checkbox" id="<?= $filter_name . $value ?>" name="<?= $filter_name ?>[]" value="<?= $value ?>" <?php if (isset($_POST[$filter_name]) && in_array($value, $_POST[$filter_name])) echo "checked"; ?>>
                    <label for="<?= $filter_name . $value ?>">
                        <?= $label ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
    </form>
</div>

<div class="grid-container">
    <?php 
    $displayed_recipes = [];
    foreach ($recipes as $recipe): 
        if (in_array($recipe['id'], $displayed_recipes)) {
            continue; 
        }
        $displayed_recipes[] = $recipe['id'];
        $recipe_id = $recipe['id'];
        $image_url = "https://www.smartcook-project.eu/api/image/{$recipe_id}.webp";
    ?>
        <a class="square-button" data-recipe-id="<?php echo $recipe['id']; ?>">
            <img src="<?php echo $image_url; ?>" alt="Button Image">
            <h2><?php echo $recipe['name']; ?></h2>
            <h1><?php echo $recipe['author']; ?></h1>
            <p>Difficulty: 
                <?php 
                if ($recipe['difficulty'] == 1) {
                    echo 'Easy';
                } elseif ($recipe['difficulty'] == 2) {
                    echo 'Normal';
                } elseif ($recipe['difficulty'] == 3) {
                    echo 'Difficult';
                } else {
                    echo 'unknown';
                }
                ?>
            </p>                
            <p>Price: 
                <?php 
                if ($recipe['price'] == 1) {
                    echo 'Cheap';
                } elseif ($recipe['price'] == 2) {
                    echo 'Normal';
                } elseif ($recipe['price'] == 3) {
                    echo 'Expensive';
                } else {
                    echo 'unknown';
                }
                ?>
            </p>
        </a>
    <?php endforeach; ?>
</div>

<div id="priceOptions" class="price-options">
</div>

<div id="recipeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="recipeDetails"></div>
    </div>
</div>
</body>
</html>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const recipeButtons = document.querySelectorAll('.square-button');
            const modal = document.getElementById('recipeModal');
            const modalContent = document.getElementById('recipeDetails');
            const span = document.getElementsByClassName('close')[0];

            recipeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const recipeId = button.getAttribute('data-recipe-id');
                    console.log('Recipe ID:', recipeId);
                    
                    fetch('get_recipe.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ recipe_id: recipeId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                        } else {
                            modalContent.innerHTML = `
                                <h2>${data.Name}</h2>
                                <p><strong>ID:</strong> ${data.ID}</p>
                                <p><strong>Difficulty:</strong> ${data.Difficulty}</p>
                                <p><strong>Duration:</strong> ${data.Duration} minutes</p>
                                <p><strong>Price:</strong> ${data.Price}</p>
                                <p><strong>Country:</strong> ${data.Country}</p>
                                <p><strong>Date and Time:</strong> ${data["Date and Time"]}</p>
                                <p><strong>Author:</strong> ${data.Author}</p>
                                <p><strong>Dish Category:</strong> ${data["Dish Category"]}</p>
                                <p><strong>Recipe Category:</strong> ${data["Recipe Category"]}</p>
                                <p><strong>Tolerance:</strong> ${data.Tolerance}</p>
                                <p><strong>Description:</strong> ${data.Description}</p>
                                <h3>Ingredients</h3>
                                <ul>
                                    ${data.Ingredients.map(i => `
                                        <li>${i.name}, Quantity: ${i.quantity}, Comment: ${i.comment}</li>
                                    `).join('')}
                                </ul>
                            `;
                            modal.style.display = "block";
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
</script>


            
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Smartcook</p>
    </footer>
<style>
    body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    overflow-y: scroll;
    height: 430vh; 
}
a
{
    text-decoration: none;
}
header {
    background-color: transparent; 
    color: rgba(255, 255, 255, 0);
    text-align: center;
    position: fixed;
    display: flex;
}

main {
    background-image: url('background.jpg');
    background-size: cover;
    background-position: center top; 
    background-attachment: fixed;
    padding: 120px 20px 20px; 
    height: 269%; 
    box-sizing: border-box;
    
}
main {

    padding-top: 80px; 
}


.recipe-filter {
    display: flex;
    justify-content: space-between;
    margin-bottom: 40px; 
}
.recipes {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    grid-gap: 20px;
}
footer {
    background-color: #2c3e5000;
    color: white;
    text-align: center;
    padding: 0.1em;
    position: fixed;
    bottom: 0;
    width: 100%;
}
header {
    position: fixed;
    display: flex;
    justify-content: space-between; 
    
}




                    /*                                                                                  Recipe holders                                                                        */

                    .square-button {
margin-left: 15%;
                        transition: transform 0.6s ease;
    text-align: center;
    width: 260px;
    height: 370px;
    border-radius: 5px;
    background-color: white;
    color: rgb(0, 0, 0);
    padding: 10px;
    cursor: pointer;
    transition: background-color 0.6s ease;
        }

.square-button img {
    width: 250px;
    height: 250px;
    border-radius: 2%;
}

.square-button p {
    text-align: block;
    margin-top: -1px;
    font-size: 14px;
        width: 100%; 
    max-width: 250px; 
    background-color: white;
    border-radius: 5px;
    transition: background-color 0.6s ease;

}

.square-button h1 {
    text-align: center;
    margin-top: -12px;
    font-size: 12px;
}

.square-button h2 {
    text-align: center;
    margin-top: 5px;
    font-size: 18px;
}

.square-button:hover {
    background-color: #ffe2cf;
    
}
.square-button:hover p{
    background-color: #ffe2cf;
    
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    padding:    ;
    row-gap: 50px;
}

@media (max-width: 1200px) {
    .grid-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .grid-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .grid-container {
        grid-template-columns: 1fr;
    }
}




#easy
{
    background-color: #00ff0d36;
}
#medium
{
    background-color: rgba(255, 166, 0, 0.274);
}
#hard
{
    background-color: #ff000036;
}




.recipe-background {
    background-size: cover;
    background-position: center;
}


    /*                                                                                                                   Chackbox hide                                             */
.tolerance-options input[type="checkbox"] {
    display: none; 
}
.price-options input[type="checkbox"] {
    display: none;
}
.difficulty-options input[type="checkbox"] {
    display: none; 
}
.dish-options input[type="checkbox"] {
    display: none; 
}
.category-options input[type="checkbox"] {
    display: none; 
}


/*                                                                                                                      Scrollbar                                           */

body::-webkit-scrollbar {
    width: 19px; 
}

body::-webkit-scrollbar-track {
    background: #000000; 
}

body::-webkit-scrollbar-thumb {
    background: #888; 
    border-radius: 3px;  
}

body::-webkit-scrollbar-thumb:hover {
    background: #555; 
}



</style>
</body>

</html>


