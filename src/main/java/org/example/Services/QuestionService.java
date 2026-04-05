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

    public void ajouter(Question q) throws SQLDataException {
        String sql = "INSERT INTO question(titre, description, date_creation, duree, note_max, user_id) " +
                "VALUES(?, ?, ?, ?, ?, ?)";  // ← ? au lieu des valeurs directes
        try {
            PreparedStatement preparedStatement = connection.prepareStatement(sql);
            preparedStatement.setString(1, q.getTitre());
            preparedStatement.setString(2, q.getDescription());
            preparedStatement.setString(3, sdf.format(q.getDateCreation()));
            preparedStatement.setInt(4, q.getDuree());
            preparedStatement.setFloat(5, q.getNoteMax());
            preparedStatement.setInt(6, q.getUserId());
            preparedStatement.executeUpdate();
            System.out.println("✅ Question ajoutée !");
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
    }

    @Override
    public void supprimer(Question q) throws SQLDataException {
        String sql = "DELETE FROM question WHERE id=" + q.getId();
        try {
            Statement statement = connection.createStatement();
            statement.executeUpdate(sql);
            System.out.println("✅ Question supprimée !");
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
    }

    @Override
    public void modifier(Question q) throws SQLDataException {
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
        }
    }

    @Override
    public List<Question> recuperer() throws SQLDataException {
        String sql = "SELECT * FROM question";
        List<Question> questionList = null;
        try {
            Statement statement = connection.createStatement();
            ResultSet rs = statement.executeQuery(sql);
            questionList = new ArrayList<>();
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
        }
        return questionList;
    }
}