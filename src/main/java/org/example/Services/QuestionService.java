package org.example.Services;

import org.example.Models.Question;
import org.example.utils.MyDatabase;
import java.sql.*;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.List;

public class QuestionService implements IService<Question> {

    private Connection connection;
    private SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd");

    public QuestionService() {
        connection = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Question q) throws SQLException {
        String sql = "INSERT INTO question(titre, description, date_creation, duree, note_max, user_id) VALUES(?, ?, ?, ?, ?, ?)";
        try {
            PreparedStatement preparedStatement = connection.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
            preparedStatement.setString(1, q.getTitre());
            preparedStatement.setString(2, q.getDescription());
            preparedStatement.setString(3, sdf.format(q.getDateCreation()));
            preparedStatement.setInt(4, q.getDuree());
            preparedStatement.setFloat(5, q.getNoteMax());
            preparedStatement.setInt(6, q.getUserId());

            preparedStatement.executeUpdate();

            ResultSet rs = preparedStatement.getGeneratedKeys();
            if (rs.next()) {
                q.setId(rs.getInt(1));
            }
            System.out.println("✅ Question ajoutée !");
        } catch (SQLException e) {
            System.out.println(e.getMessage());
            throw e;
        }
    }

    @Override
    public void supprimer(Question q) throws SQLException {
        // D'abord supprimer les choix associés
        String deleteChoixSql = "DELETE FROM choix WHERE question_id=?";
        try (PreparedStatement psChoix = connection.prepareStatement(deleteChoixSql)) {
            psChoix.setInt(1, q.getId());
            psChoix.executeUpdate();
        }

        // Ensuite supprimer la question
        String sql = "DELETE FROM question WHERE id=?";
        try (PreparedStatement preparedStatement = connection.prepareStatement(sql)) {
            preparedStatement.setInt(1, q.getId());
            preparedStatement.executeUpdate();
            System.out.println("✅ Question et ses choix supprimés !");
        }
    }

    @Override
    public void modifier(Question q) throws SQLException {
        String sql = "UPDATE question SET titre=?, description=?, date_creation=?, duree=?, note_max=?, user_id=? WHERE id=?";
        try {
            PreparedStatement preparedStatement = connection.prepareStatement(sql);
            preparedStatement.setString(1, q.getTitre());
            preparedStatement.setString(2, q.getDescription());
            preparedStatement.setString(3, sdf.format(q.getDateCreation()));
            preparedStatement.setInt(4, q.getDuree());
            preparedStatement.setFloat(5, q.getNoteMax());
            preparedStatement.setInt(6, q.getUserId());
            preparedStatement.setInt(7, q.getId());

            preparedStatement.executeUpdate();
            System.out.println("✅ Question modifiée !");
        } catch (SQLException e) {
            System.err.println(e.getMessage());
            throw e;
        }
    }

    @Override
    public List<Question> recuperer() throws SQLException {
        String sql = "SELECT * FROM question ORDER BY date_creation DESC";
        List<Question> questionList = new ArrayList<>();
        try {
            Statement statement = connection.createStatement();
            ResultSet rs = statement.executeQuery(sql);
            while (rs.next()) {
                Question q = new Question();
                q.setId(rs.getInt("id"));
                q.setTitre(rs.getString("titre"));
                q.setDescription(rs.getString("description"));
                q.setDateCreation(rs.getDate("date_creation"));
                q.setDuree(rs.getInt("duree"));
                q.setNoteMax(rs.getFloat("note_max"));
                q.setUserId(rs.getInt("user_id"));
                questionList.add(q);
            }
        } catch (SQLException e) {
            System.out.println(e.getMessage());
            throw e;
        }
        return questionList;
    }

    // Méthode supplémentaire pour récupérer une question par ID
    public Question getById(int id) throws SQLException {
        String sql = "SELECT * FROM question WHERE id = ?";
        try (PreparedStatement preparedStatement = connection.prepareStatement(sql)) {
            preparedStatement.setInt(1, id);
            ResultSet rs = preparedStatement.executeQuery();
            if (rs.next()) {
                Question q = new Question();
                q.setId(rs.getInt("id"));
                q.setTitre(rs.getString("titre"));
                q.setDescription(rs.getString("description"));
                q.setDateCreation(rs.getDate("date_creation"));
                q.setDuree(rs.getInt("duree"));
                q.setNoteMax(rs.getFloat("note_max"));
                q.setUserId(rs.getInt("user_id"));
                return q;
            }
        }
        return null;
    }
}