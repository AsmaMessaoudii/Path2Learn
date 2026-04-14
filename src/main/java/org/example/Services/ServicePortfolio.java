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
    public void supprimer(Portfolio p) throws SQLDataException {
        String sql = "DELETE FROM portfolio WHERE id=?";
        try {
            PreparedStatement ps = connection.prepareStatement(sql);
            ps.setInt(1, p.getId());
            ps.executeUpdate();
            System.out.println("✅ Portfolio supprimé !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur suppression portfolio : " + e.getMessage());
        }
    }

    // Add this method to your existing ServicePortfolio class

    public int countPortfoliosByStudentRole() throws SQLException {
        String sql = "SELECT COUNT(DISTINCT p.id) FROM portfolio p " +
                "JOIN user u ON p.user_id = u.id " +
                "WHERE u.role = 'etudiant'";

        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            if (rs.next()) {
                return rs.getInt(1);
            }
            return 0;
        }
    }

    public int countTotalStudents() throws SQLException {
        String sql = "SELECT COUNT(*) FROM user WHERE role = 'etudiant'";

        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            if (rs.next()) {
                return rs.getInt(1);
            }
            return 0;
        }
    }

    public double getPortfolioCoveragePercentage() throws SQLException {
        int studentsWithPortfolio = countPortfoliosByStudentRole();
        int totalStudents = countTotalStudents();

        if (totalStudents == 0) return 0.0;
        return (studentsWithPortfolio * 100.0) / totalStudents;
    }

    public List<String> getStudentPortfolioStatistics() throws SQLException {
        List<String> stats = new ArrayList<>();

        String sql = "SELECT " +
                "COUNT(DISTINCT u.id) as total_students, " +
                "COUNT(DISTINCT p.id) as students_with_portfolio, " +
                "(COUNT(DISTINCT u.id) - COUNT(DISTINCT p.id)) as students_without_portfolio " +
                "FROM user u " +
                "LEFT JOIN portfolio p ON u.id = p.user_id " +
                "WHERE u.role = 'etudiant'";

        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            if (rs.next()) {
                stats.add("Total étudiants: " + rs.getInt("total_students"));
                stats.add("Étudiants avec portfolio: " + rs.getInt("students_with_portfolio"));
                stats.add("Étudiants sans portfolio: " + rs.getInt("students_without_portfolio"));

                int withPortfolio = rs.getInt("students_with_portfolio");
                int total = rs.getInt("total_students");
                double percentage = total > 0 ? (withPortfolio * 100.0 / total) : 0;
                stats.add(String.format("Taux de couverture: %.1f%%", percentage));
            }
        }

        return stats;
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