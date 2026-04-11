package org.example.Services;

import org.example.Models.Cours;
import org.example.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.sql.SQLDataException;

public class ServiceCours implements IService<Cours> {

    private Connection connection;

    public ServiceCours() {
        connection = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Cours c) throws SQLDataException {
        String sql = "INSERT INTO cours(titre, description, niveau, matiere, duree, date_creation, email_prof, statut, user_id) " +
                "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setString(1, c.getTitre());
            ps.setString(2, c.getDescription());
            ps.setString(3, c.getNiveau());
            ps.setString(4, c.getMatiere());
            ps.setInt(5, c.getDuree());
            ps.setDate(6, c.getDate_creation());
            ps.setString(7, c.getEmail_prof());
            ps.setString(8, c.getStatut());
            ps.setInt(9, c.getUser_id());
            ps.executeUpdate();
            System.out.println("✅ Cours ajouté !");

            // ← ajouter cette ligne
            new ServiceBadge().onCourseAdded(c.getUser_id());

        } catch (SQLException e) {
            System.out.println("❌ Erreur ajout cours : " + e.getMessage());
        }
    }

    @Override
    public void modifier(Cours c) throws SQLDataException {
        String sql = "UPDATE cours SET titre=?, description=?, niveau=?, matiere=?, duree=?, " +
                "date_creation=?, email_prof=?, statut=?, user_id=? WHERE id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setString(1, c.getTitre());
            ps.setString(2, c.getDescription());
            ps.setString(3, c.getNiveau());
            ps.setString(4, c.getMatiere());
            ps.setInt(5, c.getDuree());
            ps.setDate(6, c.getDate_creation());
            ps.setString(7, c.getEmail_prof());
            ps.setString(8, c.getStatut());
            ps.setInt(9, c.getUser_id());
            ps.setInt(10, c.getId());
            ps.executeUpdate();
            System.out.println("✅ Cours modifié !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur modification cours : " + e.getMessage());
        }
    }

    @Override
    public void supprimer(Cours c) throws SQLDataException {
        String sql = "DELETE FROM cours WHERE id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setInt(1, c.getId());
            ps.executeUpdate();
            System.out.println("✅ Cours supprimé !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur suppression cours : " + e.getMessage());
        }
    }

    @Override
    public List<Cours> recuperer() throws SQLDataException {
        List<Cours> coursList = new ArrayList<>();
        String sql = "SELECT * FROM cours";
        try {
            Statement st = connection.createStatement();
            ResultSet rs = st.executeQuery(sql);
            while (rs.next()) {
                Cours c = new Cours();
                c.setId(rs.getInt("id"));
                c.setTitre(rs.getString("titre"));
                c.setDescription(rs.getString("description"));
                c.setNiveau(rs.getString("niveau"));
                c.setMatiere(rs.getString("matiere"));
                c.setDuree(rs.getInt("duree"));
                c.setDate_creation(rs.getDate("date_creation"));
                c.setEmail_prof(rs.getString("email_prof"));
                c.setStatut(rs.getString("statut"));
                c.setUser_id(rs.getInt("user_id"));
                coursList.add(c);
            }
        } catch (SQLException e) {
            System.out.println("❌ Erreur récupération cours : " + e.getMessage());
        }
        return coursList;
    }
}