package org.example.Services;

import org.example.Models.User;
import org.example.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
public class ServiceUser {

    private Connection connection;

    public ServiceUser() {
        connection = MyDatabase.getInstance().getConnection();
    }

    // ── AJOUTER ──────────────────────────────────────────────
    public void ajouter(User user) throws SQLException {
        String sql = "INSERT INTO user (nom, prenom, email, mot_de_passe, role, status, date_creation) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?)";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setString(1, user.getNom());
        ps.setString(2, user.getPrenom());
        ps.setString(3, user.getEmail());
        ps.setString(4, user.getMot_de_passe());
        ps.setString(5, user.getRole());
        ps.setString(6, user.getStatus());
        ps.setTimestamp(7, user.getDate_creation());
        ps.executeUpdate();
        System.out.println("User ajouté avec succès !");
    }

    // ── MODIFIER ──────────────────────────────────────────────
    public void modifier(User user) throws SQLException {
        String sql = "UPDATE user SET nom=?, prenom=?, email=?, mot_de_passe=?, role=?, status=? WHERE id=?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setString(1, user.getNom());
        ps.setString(2, user.getPrenom());
        ps.setString(3, user.getEmail());
        ps.setString(4, user.getMot_de_passe());
        ps.setString(5, user.getRole());
        ps.setString(6, user.getStatus());
        ps.setInt(7, user.getId());
        ps.executeUpdate();
        System.out.println("User modifié avec succès !");
    }

    // ── SUPPRIMER ─────────────────────────────────────────────
    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM user WHERE id = ?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, id);
        ps.executeUpdate();
        System.out.println("User supprimé avec succès !");
    }

    // ── RECUPERER TOUS ────────────────────────────────────────
    public List<User> recuperer() throws SQLException {
        String sql = "SELECT * FROM user";
        Statement st = connection.createStatement();
        ResultSet rs = st.executeQuery(sql);
        List<User> list = new ArrayList<>();
        while (rs.next()) {
            User u = new User();
            u.setId(rs.getInt("id"));
            u.setNom(rs.getString("nom"));
            u.setPrenom(rs.getString("prenom"));
            u.setEmail(rs.getString("email"));
            u.setMot_de_passe(rs.getString("mot_de_passe"));
            u.setRole(rs.getString("role"));
            u.setStatus(rs.getString("status"));
            u.setDate_creation(rs.getTimestamp("date_creation"));
            list.add(u);
        }
        return list;
    }

    // ── RECUPERER PAR ID ──────────────────────────────────────
    public User recupererById(int id) throws SQLException {
        String sql = "SELECT * FROM user WHERE id = ?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, id);
        ResultSet rs = ps.executeQuery();
        if (rs.next()) {
            User u = new User();
            u.setId(rs.getInt("id"));
            u.setNom(rs.getString("nom"));
            u.setPrenom(rs.getString("prenom"));
            u.setEmail(rs.getString("email"));
            u.setMot_de_passe(rs.getString("mot_de_passe"));
            u.setRole(rs.getString("role"));
            u.setStatus(rs.getString("status"));
            u.setDate_creation(rs.getTimestamp("date_creation"));
            return u;
        }
        return null;
    }
}