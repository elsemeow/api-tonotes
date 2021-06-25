<?php
class Note
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function fetchAllNotes()
    {
        $query = "SELECT
            notes.id, notes.title, notes.date_time, notes.todos FROM notes
            ORDER BY notes.id DESC";
        return $this->db->fetchAll($query);
    }

    public function fetchOneNote($parameter)
    {
        $query = "SELECT
            notes.id, notes.title, notes.date_time, notes.todos FROM notes
            WHERE notes.id = ?
        ";
        return $this->db->fetchOne($query, $parameter);
    }

    public function insertNote($parameters, $user_id)
    {
        $query = "INSERT INTO notes (title, user_id, date_time, todos) VALUES (?, ?, ?, ?)";
        if (isset($parameters->title)) {
            $title = $parameters->title;
            $date_time = date("d.m.Y H:i:s");
            $todos = $parameters->todos;
            $this->db->insertOne($query, $title, $user_id, $date_time, $todos);
            return $parameters;
        } else {
            return -1;
        }
    }

    public function updateNote($parameters)
    {
        $query = "UPDATE notes SET title = ?, todos = ? WHERE id = ?";
        if (isset($parameters['id']) && isset($parameters['title']) || isset($parameters['todos'])) {
            $id = $parameters['id'];
            $title = $parameters['title'];
            $todos = $parameters['todos'];
            $results = $this->db->updateOne($query, $title, $todos, $id);
            return $parameters;
        } else {
            return -1;
        }
    }

    public function deleteNote($id)
    {
        $query = "DELETE FROM notes
                  WHERE id = ?
        ";
        $results = $this->db->deleteOne($query, $id);
        return [
            "message" => "Note with the id $id was successfully deleted",
        ];
    }
}
