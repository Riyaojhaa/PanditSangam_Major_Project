<?php

require_once __DIR__ . '/../config/db.php';

function registerPandit($userId, $data){
    global $panditCollection;

    $insertData = [
        "userId"          => new MongoDB\BSON\ObjectId($userId),
        "poojaType"       => $data['pooja_type'],           // string
        "poojaExperience" => (int)$data['pooja_experience'], // int
        "certifications"  => $data['certifications'],  // array
        "aadharCardUrl"   => $data['aadhar_card_url'],// string (url from upload API)
        "travelPref"      => $data['travel_pref'],  // string
        "knownLanguage"   => $data['known_language'], // string
        "about"        => $data['about'],
        "createdAt"    => new MongoDB\BSON\UTCDateTime(),
        "updatedAt"    => new MongoDB\BSON\UTCDateTime()
    ];

    $result = $panditCollection->insertOne($insertData);

    return [
        'success'  => true,
        'panditId' => (string)$result->getInsertedId()
    ];
}

function getPanditByUserId($userId){
    global $panditCollection;
    return $panditCollection->findOne([
        'userId' => new MongoDB\BSON\ObjectId($userId)
    ]);
}

function updatePandit($userId, $data){
    global $panditCollection;

    $updateData = [];

    if (isset($data['pooja_type']))       $updateData['poojaType']       = $data['pooja_type'];
    if (isset($data['pooja_experience'])) $updateData['poojaExperience'] = (int)$data['pooja_experience'];
    if (isset($data['travel_pref']))      $updateData['travelPref']      = $data['travel_pref'];
    if (isset($data['known_language']))   $updateData['knownLanguage']   = $data['known_language'];
    if (isset($data['about']))            $updateData['about']           = $data['about'];
    if (isset($data['aadhar_card_url']))  $updateData['aadharCardUrl']   = $data['aadhar_card_url'];
    if (isset($data['certifications']))   $updateData['certifications']  = is_array($data['certifications'])
                                                                           ? $data['certifications']
                                                                           : [$data['certifications']];

    $updateData['updatedAt'] = new MongoDB\BSON\UTCDateTime();


    $result = $panditCollection->updateOne(
        ['userId' => new MongoDB\BSON\ObjectId($userId)],
        ['$set'   => $updateData]
    );

    // ✅ TEMPORARILY — file mein likho
    file_put_contents(
        "C:/xampp/htdocs/PanditAppNew/debug.txt",
        "userId: " . $userId . "\n" .
        "updateData: " . print_r($updateData, true) . "\n" .
        "matched: " . $result->getMatchedCount() . "\n" .
        "modified: " . $result->getModifiedCount() . "\n",
        FILE_APPEND
    );

    return $result->getModifiedCount() > 0;
}

function getPanditById($panditId){
    global $panditCollection;
    return $panditCollection->findOne([
        '_id' => new MongoDB\BSON\ObjectId($panditId)
    ]);
}
function getAllPandits(){
    global $panditCollection;
    $cursor  = $panditCollection->find([]);
    $pandits = [];
    foreach ($cursor as $pandit) {
        $pandits[] = $pandit;  // ✅ raw — controller mein convert hoga
    }
    return $pandits;
}