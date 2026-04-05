package org.example.Test;

import org.example.Models.Cours;
import org.example.Services.ServiceCours;

import java.sql.Date;
import java.sql.SQLException;

public class Main {
    public static void main(String[] args) throws SQLException {

        ServiceCours service = new ServiceCours();

        // ✅ AJOUTER
        Cours c = new Cours("Java JDBC", "Cours sur JDBC", "Débutant",
                "Informatique", 30, Date.valueOf("2024-01-15"),
                "prof@esprit.tn", "actif", 1);
        service.ajouter(c);
        System.out.println("=== Après ajout ===");
        service.recuperer().forEach(System.out::println);

        // ✅ MODIFIER
        Cours cModifie = new Cours("Java JDBC Modifié", "Nouvelle description", "Intermédiaire",
                "Informatique", 45, Date.valueOf("2024-06-01"),
                "prof2@esprit.tn", "inactif", 1);
        cModifie.setId(1); // ⚠️ mets l'id existant
        service.modifier(cModifie);
        System.out.println("=== Après modification ===");
        service.recuperer().forEach(System.out::println);

        // ✅ SUPPRIMER (on passe l'objet directement)
        Cours cSupprimer = new Cours();
        cSupprimer.setId(18); // ⚠️ mets l'id à supprimer
        service.supprimer(cSupprimer);
        System.out.println("=== Après suppression ===");
        service.recuperer().forEach(System.out::println);
    }
}