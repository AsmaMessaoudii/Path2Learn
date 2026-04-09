package org.example.controllers;

import javafx.fxml.FXML;
import javafx.scene.Node;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.layout.BorderPane;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.VBox;
import javafx.scene.layout.HBox;
import javafx.geometry.Insets;
import javafx.geometry.Pos;

public class DashboardController {

    @FXML
    private BorderPane rootPane;

    @FXML
    private VBox dynamicContent;

    @FXML
    private Label pageTitle;

    private Button currentActiveBtn;

    @FXML
    private void initialize() {
        // Afficher le dashboard par défaut
        showDashboard();
    }

    private void setActiveButton(Button btn) {
        if (currentActiveBtn != null) {
            currentActiveBtn.getStyleClass().remove("nav-btn-active");
        }
        currentActiveBtn = btn;
        if (currentActiveBtn != null) {
            currentActiveBtn.getStyleClass().add("nav-btn-active");
        }
    }

    @FXML
    private void handleDashboard() {
        Button dashboardBtn = (Button) ((Node) pageTitle).getScene().lookup("#dashboardBtn");
        setActiveButton(dashboardBtn);
        showDashboard();
    }

    private void showDashboard() {
        pageTitle.setText("Dashboard");
        dynamicContent.getChildren().clear();

        // Créer des cartes statistiques (exemple)
        GridPane cardsGrid = new GridPane();
        cardsGrid.setHgap(20);
        cardsGrid.setVgap(20);
        cardsGrid.setPadding(new Insets(20));

        String[][] stats = {
                {"Cours", "12", "#2ecc71"},
                {"Ressources", "48", "#3498db"},
                {"Utilisateurs", "156", "#e74c3c"},
                {"Questions", "324", "#f39c12"},
                {"Projets", "8", "#9b59b6"},
                {"Événements", "5", "#e67e22"}
        };

        int cols = 2;
        for (int i = 0; i < stats.length; i++) {
            VBox card = createStatCard(stats[i][0], stats[i][1], stats[i][2]);
            cardsGrid.add(card, i % cols, i / cols);
        }

        dynamicContent.getChildren().add(cardsGrid);
    }

    private VBox createStatCard(String title, String value, String color) {
        VBox card = new VBox(10);
        card.setStyle("-fx-background-color: white; -fx-background-radius: 12px; -fx-padding: 20px; -fx-cursor: hand;");
        card.setPrefWidth(280);

        Label titleLabel = new Label(title);
        titleLabel.setStyle("-fx-font-size: 14px; -fx-text-fill: #7f8c8d;");

        Label valueLabel = new Label(value);
        valueLabel.setStyle("-fx-font-size: 32px; -fx-font-weight: bold; -fx-text-fill: " + color + ";");

        card.getChildren().addAll(titleLabel, valueLabel);

        // Effet hover
        card.setOnMouseEntered(e ->
                card.setStyle("-fx-background-color: #f8f9fa; -fx-background-radius: 12px; -fx-padding: 20px; -fx-cursor: hand; -fx-effect: dropshadow(gaussian, rgba(0,0,0,0.15), 15, 0, 0, 4);")
        );
        card.setOnMouseExited(e ->
                card.setStyle("-fx-background-color: white; -fx-background-radius: 12px; -fx-padding: 20px; -fx-cursor: hand;")
        );

        return card;
    }

    @FXML
    private void handleCours() {
        Button coursBtn = (Button) ((Node) pageTitle).getScene().lookup("#coursBtn");
        setActiveButton(coursBtn);
        pageTitle.setText("Gestion des Cours");
        showModulePlaceholder("Cours");
    }

    @FXML
    private void handleRessources() {
        Button ressourcesBtn = (Button) ((Node) pageTitle).getScene().lookup("#ressourcesBtn");
        setActiveButton(ressourcesBtn);
        pageTitle.setText("Gestion des Ressources");
        showModulePlaceholder("Ressources");
    }

    @FXML
    private void handleUtilisateur() {
        Button utilisateurBtn = (Button) ((Node) pageTitle).getScene().lookup("#utilisateurBtn");
        setActiveButton(utilisateurBtn);
        pageTitle.setText("Gestion des Utilisateurs");
        showModulePlaceholder("Utilisateurs");
    }

    @FXML
    private void handleQuestions() {
        Button questionsBtn = (Button) ((Node) pageTitle).getScene().lookup("#questionsBtn");
        setActiveButton(questionsBtn);
        pageTitle.setText("Gestion des Questions & Choix");
        showModulePlaceholder("Questions et Choix");
    }

    @FXML
    private void handleProjets() {
        Button projetsBtn = (Button) ((Node) pageTitle).getScene().lookup("#projetsBtn");
        setActiveButton(projetsBtn);
        pageTitle.setText("Gestion des Projets & Portfolio");
        showModulePlaceholder("Projets et Portfolio");
    }

    @FXML
    private void handleEvenements() {
        Button evenementsBtn = (Button) ((Node) pageTitle).getScene().lookup("#evenementsBtn");
        setActiveButton(evenementsBtn);
        pageTitle.setText("Gestion des Événements & Participants");
        showModulePlaceholder("Événements et Participants");
    }

    private void showModulePlaceholder(String moduleName) {
        dynamicContent.getChildren().clear();

        VBox placeholder = new VBox(25);
        placeholder.setAlignment(Pos.TOP_CENTER);
        placeholder.setStyle("-fx-background-color: white; -fx-background-radius: 12px; -fx-padding: 40px;");

        // Icône du module (emoji temporaire)
        Label iconLabel = new Label();
        String icon = switch (moduleName) {
            case "Cours" -> "📚";
            case "Ressources" -> "📄";
            case "Utilisateurs" -> "👥";
            case "Questions et Choix" -> "❓";
            case "Projets et Portfolio" -> "💼";
            case "Événements et Participants" -> "🎉";
            default -> "📋";
        };
        iconLabel.setText(icon);
        iconLabel.setStyle("-fx-font-size: 48px;");

        Label title = new Label(moduleName);
        title.setStyle("-fx-font-size: 28px; -fx-font-weight: bold; -fx-text-fill: #2c3e50;");

        Label message = new Label("Module en construction - Interface CRUD à intégrer");
        message.setStyle("-fx-font-size: 14px; -fx-text-fill: #7f8c8d;");

        HBox buttons = new HBox(15);
        buttons.setAlignment(Pos.CENTER);

        Button addBtn = createActionButton("➕ Ajouter", "#2ecc71");
        Button editBtn = createActionButton("✏️ Modifier", "#3498db");
        Button deleteBtn = createActionButton("🗑️ Supprimer", "#e74c3c");
        Button viewBtn = createActionButton("👁️ Afficher", "#9b59b6");

        buttons.getChildren().addAll(addBtn, editBtn, deleteBtn, viewBtn);
        placeholder.getChildren().addAll(iconLabel, title, message, buttons);

        dynamicContent.getChildren().add(placeholder);
    }

    private Button createActionButton(String text, String color) {
        Button btn = new Button(text);
        btn.setStyle(
                "-fx-background-color: " + color + ";" +
                        "-fx-text-fill: white;" +
                        "-fx-font-size: 14px;" +
                        "-fx-padding: 12px 25px;" +
                        "-fx-background-radius: 8px;" +
                        "-fx-cursor: hand;" +
                        "-fx-font-weight: bold;"
        );

        // Effet hover
        btn.setOnMouseEntered(e ->
                btn.setStyle(
                        "-fx-background-color: " + adjustColor(color, -20) + ";" +
                                "-fx-text-fill: white;" +
                                "-fx-font-size: 14px;" +
                                "-fx-padding: 12px 25px;" +
                                "-fx-background-radius: 8px;" +
                                "-fx-cursor: hand;" +
                                "-fx-font-weight: bold;" +
                                "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.2), 5, 0, 0, 2);"
                )
        );

        btn.setOnMouseExited(e ->
                btn.setStyle(
                        "-fx-background-color: " + color + ";" +
                                "-fx-text-fill: white;" +
                                "-fx-font-size: 14px;" +
                                "-fx-padding: 12px 25px;" +
                                "-fx-background-radius: 8px;" +
                                "-fx-cursor: hand;" +
                                "-fx-font-weight: bold;"
                )
        );

        btn.setOnAction(e -> {
            System.out.println("🔘 Action: " + text + " - Module: " + pageTitle.getText());
            // Ici vous ajouterez la logique CRUD plus tard
        });

        return btn;
    }

    private String adjustColor(String color, int percent) {
        // Simple ajustement de couleur pour l'effet hover
        // Vous pouvez implémenter une vraie conversion hexadécimale si besoin
        return color;
    }

    @FXML
    private void handleLogout() {
        System.out.println("🔓 Déconnexion - Fermeture de la session");
        // Implémenter la logique de déconnexion
        // Exemple: retour à l'écran de login
    }
}