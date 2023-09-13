<?php
namespace Yohten\Api;

class Todo {
    private $db;
    private $requestMethod;
    private $todoId;

    public function __construct($db, $requestMethod, $todoId) {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->todoId = $todoId;
    }

    public function processRequest() {
        switch($this->requestMethod) {
            case 'GET':
                if ($this->todoId) {
                    $response = $this->getTodo($this->todoId);
                } else {
                    $response = $this->getAllTodos();
                }
                break;
            case 'POST':
                $response = $this->createTodo();
                break;
            case 'PUT':
                $response = $this->updateTodo($this->todoId);
                break;
            case 'DELETE':
                $response = $this->deleteTodo($this->todoId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getTodo($id) {
      $result = $this->find($id);
      if (! $result) {
          return $this->notFoundResponse();
      }
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    private function getAllTodos() {
      $query = "
        SELECT
            id, title, text, isDone
        FROM
            todos;
      ";
  
      try {
        $statement = $this->db->query($query);
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
  
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($result);
      return $response;
    }

    private function createTodo() {
      $input = (array) json_decode(file_get_contents('php://input'), TRUE);
      if (! $this->validateTodo($input)) {
        return $this->unprocessableEntityResponse();
      }
  
      $query = "
        INSERT INTO todos
            (title, text, isDone)
        VALUES
            (:title, :text, :isDone);
      ";
  
      try {
        $statement = $this->db->prepare($query);
        $statement->execute(array(
          'title' => $input['title'],
          'text'  => $input['text'],
          'isDone' => $input['isDone'],
        ));
        $statement->rowCount();
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
  
      $response['status_code_header'] = 'HTTP/1.1 201 Created';
      $response['body'] = json_encode(array('message' => 'Todo Created'));
      return $response;
    }


    private function updateTodo($id) {
    $result = $this->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateTodo($input)) {
            return $this->unprocessableEntityResponse();
        }

    $statement = "
        UPDATE todos
        SET
        title = :title,
        text  = :text,
        isDone = :isDone,
        WHERE id = :id;
    ";

    try {
        $statement = $this->db->prepare($statement);
        $statement->execute(array(
            'id' => (int) $id,
            'title' => $input['title'],
            'text' => $input['text'],
            'isDone'  => $input['isDone'],
        ));
        $statement->rowCount();
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'Todo Updated!'));
        return $response;
    }
    private function deleteTodo($id) {
        $result = $this->find($id);

        if (! $result) {
            return $this->notFoundResponse();
        }

        $query = "
            DELETE FROM todos
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array('id' => $id));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Todo Deleted!'));
        return $response;
    }

    private function validateTodo($input)
    {
      if (!isset($input['title'])) {
        return false;
      }
      if (!isset($input['text'])) {
        return false;
      }
      if (!isset($input['isDone'])) {
        return false;
      }
  
      return true;
    }

    public function find($id) {
      $query = "
        SELECT
            id, title, text, isDone
        FROM
            todos
        WHERE id = :id;
      ";
  
      try {
        $statement = $this->db->prepare($query);
        $statement->execute(array('id' => $id));
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return $result;
      } catch (\PDOException $e) {
        exit($e->getMessage());
      }
    }

    private function notFoundResponse() {
      $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
      $response['body'] = null;
      return $response;
    }


  private function unprocessableEntityResponse() {
    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = json_encode([
      'error' => 'Invalid input'
    ]);
    return $response;
  }
}