package org.example.Services;

import org.example.Models.Cours;
import org.example.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ServiceCours implements IService<Cours> {

    private Connection conn;
    private Statement ste;

    public ServiceCours() {
        conn = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Cours c) throws SQLException {
        String req = "INSERT INTO cours (titre, description, niveau, matiere, duree, date_creation, email_prof, statut, user_id) " +
                "VALUES ('" + c.getTitre() + "', '" + c.getDescription() + "', '" + c.getNiveau() + "', '" +
                c.getMatiere() + "', " + c.getDuree() + ", '" + c.getDate_creation() + "', '" +
                c.getEmail_prof() + "', '" + c.getStatut() + "', " + c.getUser_id() + ")";
        ste = conn.createStatement();
        ste.executeUpdate(req);
        System.out.println("Cours ajouté avec succès !");
    }

    @Override
    public void modifier(Cours c) throws SQLException {
        String req = "UPDATE cours SET titre='" + c.getTitre() + "', description='" + c.getDescription() +
                "', niveau='" + c.getNiveau() + "', matiere='" + c.getMatiere() +
                "', duree=" + c.getDuree() + ", date_creation='" + c.getDate_creation() +
                "', email_prof='" + c.getEmail_prof() + "', statut='" + c.getStatut() +
                "', user_id=" + c.getUser_id() + " WHERE id=" + c.getId();
        ste = conn.createStatement();
        ste.executeUpdate(req);
        System.out.println("Cours modifié avec succès !");
    }

    @Override
    public void supprimer(Cours c) throws SQLException {
        String req = "DELETE FROM cours WHERE id=" + c.getId();
        ste = conn.createStatement();
        ste.executeUpdate(req);
        System.out.println("Cours supprimé avec succès !");
    }

    @Override
    public List<Cours> recuperer() throws SQLException {
        List<Cours> liste = new ArrayList<>();
        String req = "SELECT * FROM cours";
        ste = conn.createStatement();
        ResultSet rs = ste.executeQuery(req);
        while (rs.next()) {
            Cours c = new Cours(
                    rs.getString("titre"),
                    rs.getString("description"),
                    rs.getString("niveau"),
                    rs.getString("matiere"),
                    rs.getInt("duree"),
                    rs.getDate("date_creation"),
                    rs.getString("email_prof"),
                    rs.getString("statut"),
                    rs.getInt("user_id")
            );
            c.setId(rs.getInt("id"));
            liste.add(c);
        }
        return liste;
    }
}