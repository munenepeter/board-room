<?php

namespace BoardRoom\Models;

use BoardRoom\Models\Model;
use BoardRoom\Core\Mantle\App;
use BoardRoom\Core\Database\Connection;


class Meeting extends Model
{

    public static function all()
    {
        return App::get('database')->query(
            "SELECT meetings.id, employees.username as owner, meeting_types.type as type, meeting_details.name as name, meeting_details.duration as duration, meeting_details.meeting_date, meetings.created_at
        FROM meetings
        JOIN employees ON meetings.employee_no = employees.employee_no
        JOIN meeting_types ON meetings.meeting_type_id = meeting_types.id
        JOIN meeting_details ON meetings.meeting_details_id = meeting_details.id;
        "
        );
    }

    public static function create(array $meeting)
    {

        $database = (is_dev()) ? App::get('config')['sqlite'] : App::get('config')['mysql'];

        $pdo = Connection::make($database);

        $pdo->beginTransaction();

        try
        {
            // Insert data into meeting_details table
            $insertDetails = $pdo->prepare("INSERT INTO meeting_details (name, duration, meeting_date) VALUES (:name, :duration, :meeting_date)");
            $insertDetails->execute(
                array(
                    ':name' => $meeting['name'],
                    ':duration' => $meeting['duration'],
                    ':meeting_date' => $meeting['meeting_date']
                )
            );

            // Retrieve the last inserted ID from meeting_details table
            $lastInsertId = $pdo->lastInsertId();

            // Insert data into meetings table
            $insertMeeting = $pdo->prepare("INSERT INTO meetings (meeting_type_id, employee_no, meeting_details_id, created_at) VALUES (:meeting_type_id, :employee_no, :meeting_details_id, NOW())");
            $insertMeeting->execute(
                array(
                    ':meeting_type_id' => $meeting['meeting_type'],
                    ':employee_no' => $meeting['employee_no'],
                    ':meeting_details_id' => $lastInsertId
                )
            );

            // Commit the transaction
            $pdo->commit();

            return true;
        } catch (\PDOException $e)
        {
            // Rollback the transaction if any error occurred
            $pdo->rollback();
            logger("Error", "<b>Database: An error happened" . $e->getMessage());
            return false;

        }





        // return App::get('database')->queryInsert(
        //     "
        //         START TRANSACTION;

        //         INSERT INTO meeting_details (name, duration, meeting_date)
        //         VALUES (
        //             '" . $meeting['name'] . "', 
        //             '" . $meeting['duration'] ."',
        //             '" . $meeting['meeting_date'] ."'
        //        );


        //         SET @last_insert_id := LAST_INSERT_ID();


        //         INSERT INTO meetings (meeting_type_id, employee_no, meeting_details_id, created_at)
        //         VALUES (
        //             '" . $meeting['meeting_type'] ."', 
        //             '" . $meeting['employee_no'] . "', , 
        //             @last_insert_id,
        //             NOW()
        //         );

        //         COMMIT;
        // "
        // );
    }
}