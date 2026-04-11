package org.example.Services;

import org.example.Models.Question;
import org.example.utils.MyDatabase;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.List;

/**
 * Persists a student's quiz response to the database.
 *
 * Assumes a table:
 *   reponse(id INT PK AUTO_INCREMENT,
 *           question_id INT,
 *           score FLOAT,
 *           date_reponse DATETIME)
 *
 * and a link table:
 *   reponse_choix(reponse_id INT, choix_id INT)
 *
 * Adapt the SQL to match your real schema if it differs.
 */
public class ReponseService {

    private final Connection connection;

    public ReponseService() {
        connection = MyDatabase.getInstance().getConnection();
    }

    /**
     * @param question    the Question that was answered
     * @param selectedIds IDs of Choix the student selected
     * @param score       score obtained (0 or question.getNoteMax())
     */
    public void saveResponse(Question question, List<Integer> selectedIds, int score)
            throws SQLException {

        // 1. Insert the reponse row
        String sqlReponse =
                "INSERT INTO reponse(question_id, score, date_reponse) VALUES(?, ?, ?)";

        try (PreparedStatement ps =
                     connection.prepareStatement(sqlReponse, Statement.RETURN_GENERATED_KEYS)) {

            ps.setInt(1, question.getId());
            ps.setFloat(2, score);
            ps.setString(3, LocalDateTime.now().toString());
            ps.executeUpdate();

            ResultSet keys = ps.getGeneratedKeys();
            if (keys.next()) {
                int reponseId = keys.getInt(1);

                // 2. Insert one row per selected choice
                String sqlLink =
                        "INSERT INTO reponse_choix(reponse_id, choix_id) VALUES(?, ?)";
                try (PreparedStatement psLink =
                             connection.prepareStatement(sqlLink)) {
                    for (int choixId : selectedIds) {
                        psLink.setInt(1, reponseId);
                        psLink.setInt(2, choixId);
                        psLink.addBatch();
                    }
                    psLink.executeBatch();
                }
            }
        }

        System.out.printf(
                "[ReponseService] Saved — question=%d, choices=%s, score=%d%n",
                question.getId(), selectedIds, score);
    }
}