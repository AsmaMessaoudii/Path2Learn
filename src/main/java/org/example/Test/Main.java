package org.example.Test;


import org.example.Models.*;

import org.example.Models.Choix;
import org.example.Models.Cours;
import org.example.Models.Question;
import org.example.Models.RessourcePedagogique;

import org.example.Models.Portfolio;
import org.example.Models.Projet;


import org.example.Services.ChoixService;
import org.example.Services.ServiceCours;
import org.example.Services.QuestionService;
import org.example.Services.ServiceRessourcePedagogique;
import org.example.Services.ServiceUser;
import org.example.Services.ServiceBadge;

import org.example.Services.ServicePortfolio;
import org.example.Services.ServiceProjet;

import java.sql.Date;
import java.sql.SQLException;
import java.sql.Timestamp;

public class Main {
    public static void main(String[] args) {

        ServiceCours serviceCours = new ServiceCours();
        QuestionService serviceQuestion = new QuestionService();
        ServiceRessourcePedagogique serviceRessource = new ServiceRessourcePedagogique();
        ChoixService serviceChoix = new ChoixService();

        try {
            // ==================== COURS ====================
            Cours c = new Cours("Java JDBC", "Cours sur JDBC", "Débutant",
                    "Informatique", 30, Date.valueOf("2024-01-15"),
                    "prof@esprit.tn", "actif", 1);
            serviceCours.ajouter(c);
            System.out.println("=== Après ajout Cours ===");
            serviceCours.recuperer().forEach(System.out::println);

            Cours cModifie = new Cours("Java JDBC Modifié", "Nouvelle description", "Intermédiaire",
                    "Informatique", 45, Date.valueOf("2024-06-01"),
                    "prof2@esprit.tn", "inactif", 1);
            cModifie.setId(1);
            serviceCours.modifier(cModifie);
            System.out.println("=== Après modification Cours ===");
            serviceCours.recuperer().forEach(System.out::println);

            Cours cSupprimer = new Cours();
            cSupprimer.setId(18);
            serviceCours.supprimer(cSupprimer);
            System.out.println("=== Après suppression Cours ===");
            serviceCours.recuperer().forEach(System.out::println);

            // ==================== QUESTIONS ====================
            serviceQuestion.ajouter(new Question("Question Java", "Différence JDK et JRE ?", (Date) new java.util.Date(), 30, 20.0f, 1));
            serviceQuestion.ajouter(new Question("Question SQL", "C'est quoi une clé étrangère ?", (Date) new java.util.Date(), 20, 15.0f, 1));

            serviceQuestion.modifier(new Question(120, "Question modifiée", "Nouvelle description", (Date) new java.util.Date(), 45, 18.0f, 1));

            System.out.println("=== Liste des questions ===");
            serviceQuestion.recuperer().forEach(System.out::println);

            Question aSupprimer = new Question();
            aSupprimer.setId(122);
            serviceQuestion.supprimer(aSupprimer);

            // ==================== RESSOURCES PEDAGOGIQUES ====================
            RessourcePedagogique r = new RessourcePedagogique(
                    "Cours Java PDF", "pdf", "http://example.com/java",
                    Date.valueOf("2024-01-15"), 1,
                    "java_cours.pdf", Timestamp.valueOf("2024-01-15 10:00:00")
            );
            serviceRessource.ajouter(r);
            System.out.println("=== Après ajout Ressource ===");
            serviceRessource.recuperer().forEach(System.out::println);

            RessourcePedagogique rModifie = new RessourcePedagogique(
                    "Cours Java Modifié", "video", "http://example.com/java-v2",
                    Date.valueOf("2024-06-01"), 1,
                    "java_cours_v2.mp4", Timestamp.valueOf("2024-06-01 12:00:00")
            );
            rModifie.setId(1);
            serviceRessource.modifier(rModifie);
            System.out.println("=== Après modification Ressource ===");
            serviceRessource.recuperer().forEach(System.out::println);

            RessourcePedagogique rSupprimer = new RessourcePedagogique();
            rSupprimer.setId(1);
            serviceRessource.supprimer(rSupprimer);
            System.out.println("=== Après suppression Ressource ===");
            serviceRessource.recuperer().forEach(System.out::println);

            // ==================== CHOIX ====================
            serviceChoix.ajouter(new Choix("hhh", true, 5));
            serviceChoix.ajouter(new Choix("Londres", false, 116));
            serviceChoix.ajouter(new Choix("Madrid", false, 116));

            System.out.println("=== Liste des choix ===");
            serviceChoix.recuperer().forEach(System.out::println);

            serviceChoix.modifier(new Choix(1, "Paris modifié", true, 1));
            System.out.println("=== Après modification Choix ===");
            serviceChoix.recuperer().forEach(System.out::println);

            Choix choixSupprimer = new Choix();
            choixSupprimer.setId(121);
            serviceChoix.supprimer(choixSupprimer);
            System.out.println("=== Après suppression Choix ===");
            serviceChoix.recuperer().forEach(System.out::println);


            // ==================== USER ====================
            ServiceUser serviceUser = null;
            serviceUser.ajouter(new User(
                    "Ben Ali", "Mohamed", "med@gmail.com", "1234",
                    "teacher", "actif",
                    new Timestamp(System.currentTimeMillis())
            ));
            System.out.println("=== Après ajout User ===");
            serviceUser.recuperer().forEach(System.out::println);

            serviceUser.modifier(new User(
                    1, "Ben Ali", "Ahmed", "ahmed@gmail.com", "5678",
                    "teacher", "actif",
                    new Timestamp(System.currentTimeMillis())
            ));
            System.out.println("=== Après modification User ===");
            serviceUser.recuperer().forEach(System.out::println);

            serviceUser.supprimer(1);
            System.out.println("=== Après suppression User ===");
            serviceUser.recuperer().forEach(System.out::println);



            // ==================== PORTFOLIO ====================
            ServicePortfolio servicePortfolio = new ServicePortfolio();

            servicePortfolio.ajouter(new Portfolio("Mon Portfolio", "Description de mon portfolio", new Date(System.currentTimeMillis()), new Date(System.currentTimeMillis()), 1));
            System.out.println("=== Après ajout Portfolio ===");
            servicePortfolio.recuperer().forEach(System.out::println);

            Portfolio pModifie = new Portfolio("Mon Portfolio Modifié", "Nouvelle description", new Date(System.currentTimeMillis()), new Date(System.currentTimeMillis()), 1);
            pModifie.setId(1);
            servicePortfolio.modifier(pModifie);
            System.out.println("=== Après modification Portfolio ===");
            servicePortfolio.recuperer().forEach(System.out::println);

            Portfolio pSupprimer = new Portfolio();
            pSupprimer.setId(1);
            servicePortfolio.supprimer(pSupprimer);
            System.out.println("=== Après suppression Portfolio ===");
            servicePortfolio.recuperer().forEach(System.out::println);

// ==================== PROJET ====================
            ServiceProjet serviceProjet = new ServiceProjet();

            serviceProjet.ajouter(new Projet("Projet JavaFX", "Application desktop", "Projet de gestion", "Java, JavaFX, MySQL", new Date(System.currentTimeMillis()), "https://github.com/demo", 1));
            System.out.println("=== Après ajout Projet ===");
            serviceProjet.recuperer().forEach(System.out::println);

            Projet projetModifie = new Projet("Projet JavaFX Modifié", "Application modifiée", "Nouvelle description", "Java, Spring", new Date(System.currentTimeMillis()), "https://github.com/demo-v2", 1);
            projetModifie.setId(1);
            serviceProjet.modifier(projetModifie);
            System.out.println("=== Après modification Projet ===");
            serviceProjet.recuperer().forEach(System.out::println);

            Projet projetSupprimer = new Projet();
            projetSupprimer.setId(1);
            serviceProjet.supprimer(projetSupprimer);
            System.out.println("=== Après suppression Projet ===");
            serviceProjet.recuperer().forEach(System.out::println);

            System.out.println("=== Projets du portfolio 1 ===");
            serviceProjet.recupererParPortfolio(1).forEach(System.out::println);



            // ==================== BADGE (ADMIN CRUD) ====================
            ServiceBadge serviceBadge = new ServiceBadge();

            serviceBadge.ajouter(new Badge("Beginner",    "1 cours",   "bronze.png",  1,  new Timestamp(System.currentTimeMillis())));
            serviceBadge.ajouter(new Badge("Rising Star", "3 cours",   "star.png",    3,  new Timestamp(System.currentTimeMillis())));
            serviceBadge.ajouter(new Badge("Expert",      "5 cours",   "fire.png",    5,  new Timestamp(System.currentTimeMillis())));
            serviceBadge.ajouter(new Badge("Legend",      "10 cours",  "diamond.png", 10, new Timestamp(System.currentTimeMillis())));

            System.out.println("=== Tous les badges ===");
            serviceBadge.recuperer().forEach(System.out::println);

            serviceBadge.modifier(new Badge(1, "Beginner+", "Premier cours !", "bronze2.png", 1,
                    new Timestamp(System.currentTimeMillis())));

            serviceBadge.supprimer(4);

// SYSTEM — status teacher user_id=1
            serviceBadge.printStatus(1);

        } catch (SQLException e) {
            throw new RuntimeException(e);
        }
    }
}