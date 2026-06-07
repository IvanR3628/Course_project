<?php

    function createPoetryFile() {
        $file = dirname(__DIR__) . '\data\poetry.json';
        $data = ['poetry' => []];
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    function getPoems() {
        $file = dirname(__DIR__) . '\data\poetry.json';
        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['poetry'])) {
            createPoetryFile();
            return [];
        }
        return $data['poetry'];
    }

    function saveAllPoems($poems) {
        $file = dirname(__DIR__) . '\data\poetry.json';
        file_put_contents($file, json_encode(['poetry' => $poems], JSON_PRETTY_PRINT));
    }

    function findPoemById($id) {
        $poems = getPoems();
        foreach ($poems as $poem) {
            if ($poem['id'] == $id) {
                return $poem;
            }
        }
        return null;
    }

    function createNewPoem($title, $content, $authorid, $description = "", $anonymity = "n", $author = "", $unsafeage = "n"){
        $poems = getPoems();
        if (count($poems) === 0){
            $newId = 1;
        } else {
            $newId = $poems[count($poems) - 1]['id'] + 1;
        }
        $newPoem = [
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
        
        $poems[] = $newPoem;
        saveAllPoems($poems);
        return ['poem' => $newPoem];
    }

    function updatePoemById($id, $data){
        $allpoems = getPoems();
        $index = -1;

        foreach ($allpoems as $key => $poem) {
            if ($poem['id'] == $id) {
                $index = $key;
                break;
            }
        }
        
        if (isset($data['title'])) {
            $allpoems[$index]['title'] = $data['title'];
        }
        if (isset($data['content'])) {
            $allpoems[$index]['content'] = $data['content'];
        }
        if (isset($data['description'])) {
            $allpoems[$index]['description'] = $data['description'];
        }
        if (isset($data['authorid'])) {
            $allpoems[$index]['authorid'] = $data['authorid'];
        }
        if (isset($data['anonymity'])) {
            $allpoems[$index]['anonymity'] = $data['anonymity'];
        }
        if (isset($data['author'])) {
            $allpoems[$index]['author'] = $data['author'];
        }
        if (isset($data['unsafeage'])) {
            $allpoems[$index]['unsafeage'] = $data['unsafeage'];
        }
        if (isset($data['changedate'])) {
            $allpoems[$index]['changedate'] = $data['changedate'];
        }
        
        
        saveAllPoems($poems);
        return ['poem' => $allpoems[$index]];
        
    }

    function deletePoemById($id){
        $allpoems = getPoems();
        $index = -1;

        foreach ($allpoems as $key => $poem) {
            if ($poem['id'] == $id) {
                $index = $key;
                break;
            }
        }
        
        $deletedPoem = $allpoems[$index];
        array_splice($allpoems, $index, 1);
        
        saveAllPoems($allpoems);
        return ['poem' => $deletedPoem];
    }

?>