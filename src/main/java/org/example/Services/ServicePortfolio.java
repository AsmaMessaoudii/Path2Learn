package org.example.Services;

import org.example.Models.Portfolio;
import org.example.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.sql.SQLDataException;

public class ServicePortfolio implements IService<Portfolio> {

    private Connection connection;

    public ServicePortfolio() {
        connection = MyDatabase.getInstance().getConnection();
    }

    @Override
    public void ajouter(Portfolio p) throws SQLDataException {
        String sql = "INSERT INTO portfolio(titre, description, date_creation, date_mise_ajour, user_id) " +
                "VALUES(?, ?, ?, ?, ?)";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setString(1, p.getTitre());
            ps.setString(2, p.getDescription());
            ps.setDate(3, new java.sql.Date(p.getDateCreation().getTime()));
            ps.setDate(4, new java.sql.Date(p.getDateMiseAjour().getTime()));
            ps.setInt(5, p.getUserId());
            ps.executeUpdate();
            System.out.println("✅ Portfolio ajouté !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur ajout portfolio : " + e.getMessage());
        }
    }

    @Override
    public void modifier(Portfolio p) throws SQLDataException {
        String sql = "UPDATE portfolio SET titre=?, description=?, date_creation=?, date_mise_ajour=?, user_id=? WHERE id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setString(1, p.getTitre());
            ps.setString(2, p.getDescription());
            ps.setDate(3, new java.sql.Date(p.getDateCreation().getTime()));
            ps.setDate(4, new java.sql.Date(p.getDateMiseAjour().getTime()));
            ps.setInt(5, p.getUserId());
            ps.setInt(6, p.getId());
            ps.executeUpdate();
            System.out.println("✅ Portfolio modifié !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur modification portfolio : " + e.getMessage());
        }
    }

    @Override
    public void supprimer(Portfolio p) throws SQLException {
        // First delete all projects of this portfolio
        String deleteProjets = "DELETE FROM projet WHERE portfolio_id=?";
        try {
            PreparedStatement ps1 = connection.prepareStatement(deleteProjets);
            ps1.setInt(1, p.getId());
            ps1.executeUpdate();
            System.out.println("✅ Projets du portfolio supprimés !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur suppression projets : " + e.getMessage());
        }

        // Then delete the portfolio
        String deletePortfolio = "DELETE FROM portfolio WHERE id=?";
        try {
            PreparedStatement ps2 = connection.prepareStatement(deletePortfolio);
            ps2.setInt(1, p.getId());
            ps2.executeUpdate();
            System.out.println("✅ Portfolio supprimé !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur suppression portfolio : " + e.getMessage());
        }
    }

    @Override
    public List<Portfolio> recuperer() throws SQLDataException {
        List<Portfolio> portfolioList = new ArrayList<>();
        String sql = "SELECT * FROM portfolio";
        try {
            Statement st = connection.createStatement();
            ResultSet rs = st.executeQuery(sql);
            while (rs.next()) {
                Portfolio p = new Portfolio();
                p.setId(rs.getInt("id"));
                p.setTitre(rs.getString("titre"));
                p.setDescription(rs.getString("description"));
                p.setDateCreation(rs.getDate("date_creation"));
                p.setDateMiseAjour(rs.getDate("date_mise_ajour"));
                p.setUserId(rs.getInt("user_id"));
                portfolioList.add(p);
            }
        } catch (SQLException e) {
            System.out.println("❌ Erreur récupération portfolios : " + e.getMessage());
        }
        return portfolioList;
    }
}