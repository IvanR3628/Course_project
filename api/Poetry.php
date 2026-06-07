<?php

    function createPoetryFile() {
        $file = dirname(__DIR__) . '\data\poetry.json';
        $data = ['poetry' => []];
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    function getPoetry() {
        $file = dirname(__DIR__) . '\data\poetry.json';
        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['poetry'])) {
            createPoetryFile();
            return [];
        }
        return $data['poetry'];
    }

    function saveAllPoetry($poetry) {
        $file = dirname(__DIR__) . '\data\poetry.json';
        file_put_contents($file, json_encode(['poetry' => $poetry], JSON_PRETTY_PRINT));
    }

    function findPoemById($id) {
        $poetry = getPoetry();
        foreach ($poetry as $poem) {
            if ($poem['id'] == $id) {
                return $poem;
            }
        }
        return null;
    }

    function createNewPoetry($title, $content, $description, $authorid, $anonymity, $author, $unsafeage){
        $poetry = getPoetry();
        if (count($poetry) === 0){
            $newId = 1;
        } else {
            $newId = $poetry[count($poetry) - 1]['id'] + 1;
        }
        $newPoetry = [
            'id' => $newId,
            'title' => $title,
            'content' => $content,
            'description' => $description,
            'authorid' => $authorid,
            'anonymity' => $anonymity,
            'author' => $author,
            'unsafeage' => $unsafeage,
            'changedate' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ];
        
        $poetry[] = $newPoetry;
        saveAllPoetry($poetry);
        return ['poetry' => $newPoetry];
    }

    function deletePoemById($id){
        $allpoetry = getPoetry();
        $index = -1;

        foreach ($allpoetry as $key => $poetry) {
            if ($poetry['id'] == $id) {
                $index = $key;
                break;
            }
        }
        
        $deletedPoetry = $allpoetry[$index];
        array_splice($allpoetry, $index, 1);
        
        saveAllPoetry($allpoetry);
        return ['poetry' => $deletedPoetry];
    }

?>