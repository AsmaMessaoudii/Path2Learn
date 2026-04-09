package org.example.Services;

import org.example.Models.Projet;
import org.example.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.sql.SQLDataException;

public class ServiceProjet implements IService<Projet> {

    private Connection connection;

    public ServiceProjet() {
        connection = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Projet p) throws SQLDataException {
        String sql = "INSERT INTO projet(titre_projet, text, description, technologies, date_realisation, lien_demo, portfolio_id) " +
                "VALUES(?, ?, ?, ?, ?, ?, ?)";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setString(1, p.getTitreProjet());
            ps.setString(2, p.getText());
            ps.setString(3, p.getDescription());
            ps.setString(4, p.getTechnologies());
            ps.setDate(5, new java.sql.Date(p.getDateRealisation().getTime()));
            ps.setString(6, p.getLienDemo());
            ps.setInt(7, p.getPortfolioId());
            ps.executeUpdate();
            System.out.println("✅ Projet ajouté !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur ajout projet : " + e.getMessage());
        }
    }

    @Override
    public void modifier(Projet p) throws SQLDataException {
        String sql = "UPDATE projet SET titre_projet=?, text=?, description=?, technologies=?, " +
                "date_realisation=?, lien_demo=?, portfolio_id=? WHERE id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setString(1, p.getTitreProjet());
            ps.setString(2, p.getText());
            ps.setString(3, p.getDescription());
            ps.setString(4, p.getTechnologies());
            ps.setDate(5, new java.sql.Date(p.getDateRealisation().getTime()));
            ps.setString(6, p.getLienDemo());
            ps.setInt(7, p.getPortfolioId());
            ps.setInt(8, p.getId());
            ps.executeUpdate();
            System.out.println("✅ Projet modifié !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur modification projet : " + e.getMessage());
        }
    }

    @Override
    public void supprimer(Projet p) throws SQLDataException {
        String sql = "DELETE FROM projet WHERE id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setInt(1, p.getId());
            ps.executeUpdate();
            System.out.println("✅ Projet supprimé !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur suppression projet : " + e.getMessage());
        }
    }

    @Override
    public List<Projet> recuperer() throws SQLDataException {
        List<Projet> projetList = new ArrayList<>();
        String sql = "SELECT * FROM projet";
        try {
            Statement st = connection.createStatement();
            ResultSet rs = st.executeQuery(sql);
            while (rs.next()) {
                Projet p = new Projet();
                p.setId(rs.getInt("id"));
                p.setTitreProjet(rs.getString("titre_projet"));
                p.setText(rs.getString("text"));
                p.setDescription(rs.getString("description"));
                p.setTechnologies(rs.getString("technologies"));
                p.setDateRealisation(rs.getDate("date_realisation"));
                p.setLienDemo(rs.getString("lien_demo"));
                p.setPortfolioId(rs.getInt("portfolio_id"));
                projetList.add(p);
            }
        } catch (SQLException e) {
            System.out.println("❌ Erreur récupération projets : " + e.getMessage());
        }
        return projetList;
    }

    // Extra method: get projects by portfolio
    public List<Projet> recupererParPortfolio(int portfolioId) throws SQLDataException {
        List<Projet> projetList = new ArrayList<>();
        String sql = "SELECT * FROM projet WHERE portfolio_id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setInt(1, portfolioId);
            ResultSet rs = ps.executeQuery();
            while (rs.next()) {
                Projet p = new Projet();
                p.setId(rs.getInt("id"));
                p.setTitreProjet(rs.getString("titre_projet"));
                p.setText(rs.getString("text"));
                p.setDescription(rs.getString("description"));
                p.setTechnologies(rs.getString("technologies"));
                p.setDateRealisation(rs.getDate("date_realisation"));
                p.setLienDemo(rs.getString("lien_demo"));
                p.setPortfolioId(rs.getInt("portfolio_id"));
                projetList.add(p);
            }
        } catch (SQLException e) {
            System.out.println("❌ Erreur récupération projets par portfolio : " + e.getMessage());
        }
        return projetList;
    }
}