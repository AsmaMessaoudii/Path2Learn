package org.example.Services;

import org.example.Models.RessourcePedagogique;
import org.example.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ServiceRessourcePedagogique implements IService<RessourcePedagogique> {

    private Connection conn;
    private Statement ste;

    public ServiceRessourcePedagogique() {
        conn = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(RessourcePedagogique r) throws SQLException {
        String req = "INSERT INTO ressource_pedagogique (titre, type, url, date_ajout, cours_id, file_name, updated_at) " +
                "VALUES ('" + r.getTitre() + "', '" + r.getType() + "', '" + r.getUrl() +
                "', '" + r.getDate_ajout() + "', " + r.getCours_id() +
                ", '" + r.getFile_name() + "', '" + r.getUpdated_at() + "')";
        ste = conn.createStatement();
        ste.executeUpdate(req);
        System.out.println("Ressource pédagogique ajoutée avec succès !");
    }

    @Override
    public void modifier(RessourcePedagogique r) throws SQLException {
        String req = "UPDATE ressource_pedagogique SET titre='" + r.getTitre() + "', type='" + r.getType() +
                "', url='" + r.getUrl() + "', date_ajout='" + r.getDate_ajout() +
                "', cours_id=" + r.getCours_id() + ", file_name='" + r.getFile_name() +
                "', updated_at='" + r.getUpdated_at() + "' WHERE id=" + r.getId();
        ste = conn.createStatement();
        ste.executeUpdate(req);
        System.out.println("Ressource pédagogique modifiée avec succès !");
    }

    @Override
    public void supprimer(RessourcePedagogique r) throws SQLException {
        String req = "DELETE FROM ressource_pedagogique WHERE id=" + r.getId();
        ste = conn.createStatement();
        ste.executeUpdate(req);
        System.out.println("Ressource pédagogique supprimée avec succès !");
    }

    @Override
    public List<RessourcePedagogique> recuperer() throws SQLException {
        List<RessourcePedagogique> liste = new ArrayList<>();
        String req = "SELECT * FROM ressource_pedagogique";
        ste = conn.createStatement();
        ResultSet rs = ste.executeQuery(req);
        while (rs.next()) {
            RessourcePedagogique r = new RessourcePedagogique(
                    rs.getString("titre"),
                    rs.getString("type"),
                    rs.getString("url"),
                    rs.getDate("date_ajout"),
                    rs.getInt("cours_id"),
                    rs.getString("file_name"),
                    rs.getTimestamp("updated_at")
            );
            r.setId(rs.getInt("id"));
            liste.add(r);
        }
        return liste;
    }
}