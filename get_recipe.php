<?php
require_once "SmartCookClient.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$recipeId = $input['recipe_id'] ?? 0;

if (!$recipeId) {
    echo json_encode(['error' => 'Recipe ID not provided']);
    exit;
}

try {
    $smartCookClient = new SmartCookClient();
    $response = $smartCookClient
        ->setRequestData(['recipe_id' => $recipeId])
        ->sendRequest('recipe')
        ->getResponseData();

    if (empty($response['data'])) {
        echo json_encode(['error' => 'Recipe not found']);
        exit;
    }

    $r = $response['data'];

    $structureResponse = $smartCookClient
        ->sendRequest('structure')
        ->getResponseData();

    if (empty($structureResponse['data'])) {
        echo json_encode(['error' => 'Structure not found']);
        exit;
    }

    $structure = $structureResponse['data'];

    $dish_category = array_map(fn($key) => $structure['dish_category'][$key], $r['dish_category']);
    $recipe_category = array_map(fn($key) => $structure['recipe_category'][$key], $r['recipe_category']);
    $tolerance = array_map(fn($key) => $structure['tolerance'][$key], $r['tolerance']);

    $details = [
        'ID' => $r['id'],
        'Name' => ucfirst($r['name']),
        'Difficulty' => $structure['difficulty'][$r['difficulty']],
        'Duration' => $r['duration'],
        'Price' => $structure['price'][$r['price']],
        'Country' => $r['country'],
        'Date and Time' => substr($r['dttm'], 0, 10),
        'Author' => $r['author'],
        'Dish Category' => implode(', ', $dish_category),
        'Recipe Category' => implode(', ', $recipe_category),
        'Tolerance' => implode(', ', $tolerance),
        'Description' => $r['description'],
        'Ingredients' => array_map(fn($i) => [
            'name' => $i['name'],
            'quantity' => $i['quantity'] . ' ' . $i['unit'],
            'comment' => $i['comment']
        ], $r['ingredient'])
    ];

    echo json_encode($details);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
