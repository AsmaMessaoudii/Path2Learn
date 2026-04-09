package org.example.Services;

import org.example.Models.Badge;
import org.example.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ServiceBadge {

    private Connection connection;

    public ServiceBadge() {
        connection = MyDatabase.getInstance().getConnection();
    }

    // ══════════════════════════════════════════
    //  ADMIN CRUD
    // ══════════════════════════════════════════

    public void ajouter(Badge badge) throws SQLException {
        String sql = "INSERT INTO badge (name, description, icon, required_courses, created_at) " +
                "VALUES (?, ?, ?, ?, ?)";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setString(1, badge.getName());
        ps.setString(2, badge.getDescription());
        ps.setString(3, badge.getIcon());
        ps.setInt(4, badge.getRequired_courses());
        ps.setTimestamp(5, badge.getCreated_at());
        ps.executeUpdate();
        System.out.println("✅ Badge créé : " + badge.getName());
    }

    public void modifier(Badge badge) throws SQLException {
        String sql = "UPDATE badge SET name=?, description=?, icon=?, required_courses=? WHERE id=?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setString(1, badge.getName());
        ps.setString(2, badge.getDescription());
        ps.setString(3, badge.getIcon());
        ps.setInt(4, badge.getRequired_courses());
        ps.setInt(5, badge.getId());
        ps.executeUpdate();
        System.out.println("✅ Badge modifié : " + badge.getName());
    }

    public void supprimer(int id) throws SQLException {
        String sql = "DELETE FROM badge WHERE id=?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, id);
        ps.executeUpdate();
        System.out.println("✅ Badge supprimé : id=" + id);
    }

    public List<Badge> recuperer() throws SQLException {
        List<Badge> list = new ArrayList<>();
        ResultSet rs = connection.createStatement()
                .executeQuery("SELECT * FROM badge ORDER BY required_courses ASC");
        while (rs.next()) list.add(mapRow(rs));
        return list;
    }

    public Badge recupererById(int id) throws SQLException {
        String sql = "SELECT * FROM badge WHERE id=?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, id);
        ResultSet rs = ps.executeQuery();
        if (rs.next()) return mapRow(rs);
        return null;
    }

    // ══════════════════════════════════════════
    //  POINTS — calculated from cours table
    //  points = COUNT(cours WHERE user_id) * 10
    //  no extra table, no changes to user/cours
    // ══════════════════════════════════════════

    public int getPoints(int user_id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM cours WHERE user_id=?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, user_id);
        ResultSet rs = ps.executeQuery();
        if (rs.next()) return rs.getInt(1) * 10;
        return 0;
    }

    public int getCourseCount(int user_id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM cours WHERE user_id=?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, user_id);
        ResultSet rs = ps.executeQuery();
        if (rs.next()) return rs.getInt(1);
        return 0;
    }

    // ══════════════════════════════════════════
    //  BADGE LOGIC
    //  condition : required_courses <= courseCount
    //  auto granted — no join table needed
    // ══════════════════════════════════════════

    // current badge = highest badge unlocked
    public Badge getCurrentBadge(int user_id) throws SQLException {
        int courseCount = getCourseCount(user_id);
        Badge current = null;
        for (Badge b : recuperer()) {
            if (courseCount >= b.getRequired_courses()) current = b;
        }
        return current;
    }

    // next badge to unlock
    public Badge getNextBadge(int user_id) throws SQLException {
        int courseCount = getCourseCount(user_id);
        for (Badge b : recuperer()) {
            if (courseCount < b.getRequired_courses()) return b;
        }
        return null;
    }

    // all earned badges
    public List<Badge> getEarnedBadges(int user_id) throws SQLException {
        int courseCount = getCourseCount(user_id);
        List<Badge> earned = new ArrayList<>();
        for (Badge b : recuperer()) {
            if (courseCount >= b.getRequired_courses()) earned.add(b);
        }
        return earned;
    }

    // called automatically when a cours is added
    public void onCourseAdded(int user_id) throws SQLException {
        int points = getPoints(user_id);
        int courseCount = getCourseCount(user_id);
        Badge current = getCurrentBadge(user_id);
        Badge next = getNextBadge(user_id);

        System.out.println("💎 +10 points → total : " + points + " pts (" + courseCount + " cours)");

        if (current != null)
            System.out.println("🏅 Badge actuel : " + current.getName() + " " + current.getIcon());
        else
            System.out.println("🏅 Pas encore de badge");

        if (next != null)
            System.out.println("🎯 Prochain badge : " + next.getName()
                    + " — encore " + (next.getRequired_courses() - courseCount) + " cours");
        else
            System.out.println("👑 MAX atteint — tu es une Légende !");
    }

    // full status
    public void printStatus(int user_id) throws SQLException {
        int points = getPoints(user_id);
        int courseCount = getCourseCount(user_id);
        Badge current = getCurrentBadge(user_id);
        Badge next = getNextBadge(user_id);

        System.out.println("════════════════════════════════");
        System.out.println("  Status teacher — user " + user_id);
        System.out.println("════════════════════════════════");
        System.out.println("  📚 Cours postés : " + courseCount);
        System.out.println("  💎 Points       : " + points);

        if (current != null)
            System.out.println("  🏅 Badge actuel : " + current.getName()
                    + " " + current.getIcon());
        else
            System.out.println("  🏅 Badge actuel : aucun encore");

        if (next != null)
            System.out.println("  🎯 Prochain     : " + next.getName()
                    + " dans " + (next.getRequired_courses() - courseCount) + " cours");
        else
            System.out.println("  👑 Tu as tous les badges !");

        System.out.println("  📋 Badges gagnés :");
        List<Badge> earned = getEarnedBadges(user_id);
        if (earned.isEmpty())
            System.out.println("     aucun encore");
        else
            earned.forEach(b -> System.out.println(
                    "     ✔ " + b.getName()
                            + " (dès " + b.getRequired_courses() + " cours)"));
        System.out.println("════════════════════════════════");
    }

    // ══════════════════════════════════════════
    //  PRIVATE HELPER
    // ══════════════════════════════════════════
    private Badge mapRow(ResultSet rs) throws SQLException {
        Badge b = new Badge();
        b.setId(rs.getInt("id"));
        b.setName(rs.getString("name"));
        b.setDescription(rs.getString("description"));
        b.setIcon(rs.getString("icon"));
        b.setRequired_courses(rs.getInt("required_courses"));
        b.setCreated_at(rs.getTimestamp("created_at"));
        return b;
    }
}