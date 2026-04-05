package org.example.Test;

import org.example.Models.Choix;
import org.example.Models.Cours;
import org.example.Models.Question;
import org.example.Services.ChoixService;
import org.example.Services.ServiceCours;
import org.example.Services.QuestionService;

import java.sql.Date;
import java.sql.SQLDataException;

public class Main {
    public static void main(String[] args) {

        ServiceCours serviceCours = new ServiceCours();
        QuestionService serviceQuestion = new QuestionService();
        ChoixService serviceChoix = new ChoixService();

        try {
            // ==================== COURS ====================
            // ➕ Ajouter un cours
            Cours c = new Cours("Java JDBC", "Cours sur JDBC", "Débutant",
                    "Informatique", 30, Date.valueOf("2024-01-15"),
                    "prof@esprit.tn", "actif", 1);
            serviceCours.ajouter(c);
            System.out.println("=== Après ajout Cours ===");
            serviceCours.recuperer().forEach(System.out::println);

            // ✏️ Modifier un cours
            Cours cModifie = new Cours("Java JDBC Modifié", "Nouvelle description", "Intermédiaire",
                    "Informatique", 45, Date.valueOf("2024-06-01"),
                    "prof2@esprit.tn", "inactif", 1);
            cModifie.setId(1);
            serviceCours.modifier(cModifie);
            System.out.println("=== Après modification Cours ===");
            serviceCours.recuperer().forEach(System.out::println);

            // 🗑️ Supprimer un cours
            Cours cSupprimer = new Cours();
            cSupprimer.setId(18);
            serviceCours.supprimer(cSupprimer);
            System.out.println("=== Après suppression Cours ===");
            serviceCours.recuperer().forEach(System.out::println);

            // ==================== QUESTION ====================
            // ➕ Ajouter des questions
            serviceQuestion.ajouter(new Question("Question Java", "Différence JDK et JRE ?", new java.util.Date(), 30, 20.0f, 1));
            serviceQuestion.ajouter(new Question("Question SQL", "C'est quoi une clé étrangère ?", new java.util.Date(), 20, 15.0f, 1));

            // ✏️ Modifier une question
            serviceQuestion.modifier(new Question(120, "Question modifiée", "Nouvelle description", new java.util.Date(), 45, 18.0f, 1));

            // 📋 Afficher toutes les questions
            System.out.println("=== Liste des questions ===");
            serviceQuestion.recuperer().forEach(System.out::println);

            // 🗑️ Supprimer une question
            Question aSupprimer = new Question();
            aSupprimer.setId(122);
            serviceQuestion.supprimer(aSupprimer);

            // ==================== CHOIX ====================
            // ➕ Ajouter des choix
            serviceChoix.ajouter(new Choix("Paris", true, 116));
            serviceChoix.ajouter(new Choix("Londres", false, 116));
            serviceChoix.ajouter(new Choix("Madrid", false, 116));

            // 📋 Afficher tous les choix
            System.out.println("=== Liste des choix ===");
            serviceChoix.recuperer().forEach(System.out::println);

            // ✏️ Modifier un choix
            serviceChoix.modifier(new Choix(1, "Paris modifié", true, 1));
            System.out.println("=== Après modification Choix ===");
            serviceChoix.recuperer().forEach(System.out::println);

            // 🗑️ Supprimer un choix
            Choix choixSupprimer = new Choix();
            choixSupprimer.setId(121);
            serviceChoix.supprimer(choixSupprimer);
            System.out.println("=== Après suppression Choix ===");
            serviceChoix.recuperer().forEach(System.out::println);

        } catch (SQLDataException e) {
            throw new RuntimeException(e);
        }
    }
}