package org.example.Services;

import org.example.Models.Choix;
import org.example.utils.MyDatabase;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ChoixService implements IService<Choix> {

    private Connection connection;

    public ChoixService() {
        connection = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Choix c) throws SQLDataException {
        String sql = "INSERT INTO choix(contenu, est_correct, question_id) VALUES(?, ?, ?)";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setString(1, c.getContenu());
            ps.setBoolean(2, c.isEstCorrect());
            ps.setInt(3, c.getQuestionId());
            ps.executeUpdate();
            System.out.println("✅ Choix ajouté !");
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
    }

    @Override
    public void supprimer(Choix c) throws SQLDataException {
        String sql = "DELETE FROM choix WHERE id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setInt(1, c.getId());
            ps.executeUpdate();
            System.out.println("✅ Choix supprimé !");
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
    }

    @Override
    public void modifier(Choix c) throws SQLDataException {
        String sql = "UPDATE choix SET contenu=?, est_correct=?, question_id=? WHERE id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setString(1, c.getContenu());
            ps.setBoolean(2, c.isEstCorrect());
            ps.setInt(3, c.getQuestionId());
            ps.setInt(4, c.getId());
            ps.executeUpdate();
            System.out.println("✅ Choix modifié !");
        } catch (SQLException e) {
            System.err.println(e.getMessage());
        }
    }

    @Override
    public List<Choix> recuperer() throws SQLDataException {
        String sql = "SELECT * FROM choix";
        List<Choix> choixList = null;
        try {
            Statement statement = connection.createStatement();
            ResultSet rs = statement.executeQuery(sql);
            choixList = new ArrayList<>();
            while (rs.next()) {
                Choix c = new Choix();
                c.setId(rs.getInt("id"));
                c.setContenu(rs.getString("contenu"));
                c.setEstCorrect(rs.getBoolean("est_correct"));
                c.setQuestionId(rs.getInt("question_id"));
                choixList.add(c);
            }
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
        return choixList;
    }
}