package org.example.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.ProgressBar;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;
import org.example.Models.User;
import org.example.Services.ServiceBadge;


import org.example.Models.Badge;
import java.util.List;

// ← AJOUTER
import java.io.IOException;

public class HomePageController {

    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;
    @FXML private Button loginBtn, registerBtn, adminBtn;
    @FXML private VBox adminSection;
    @FXML private VBox coursCard, ressourcesCard, questionsCard, projetsCard, evenementsCard, utilisateursCard;
    @FXML private Label userLabel;
    @FXML private Button logoutBtn;

    @FXML private Label emailLabel;
    @FXML private Label roleLabel;
    @FXML private VBox profileMenu;

    @FXML private VBox badgeBox;
    @FXML private Label badgeLabel;
    @FXML private Label progressLabel;
    @FXML private javafx.scene.layout.HBox badgeShowcaseBox;
    @FXML private VBox badgeSection;

    @FXML private Label pointsLabel;
    @FXML private Label currentBadgeLabel;
    @FXML private Label nextBadgeLabel;
    @FXML private ProgressBar badgeProgress;

    private User currentUser;
    private ServiceBadge serviceBadge = new ServiceBadge();
    private int userId; // teacher id connecté

    public void setCurrentUser(User user) {
        this.currentUser = user;
        this.userId = user.getId();
        loadTeacherBadge();


        if (userLabel != null) {
            userLabel.setText("👤 " + user.getNom());

            emailLabel.setText("Email: " + user.getEmail());
            roleLabel.setText("Role: " + user.getRole());

        }

        loginBtn.setVisible(false);
        loginBtn.setManaged(false);

        registerBtn.setVisible(false);
        registerBtn.setManaged(false);

        logoutBtn.setVisible(true);
        logoutBtn.setManaged(true);

        System.out.println("Welcome " + user.getNom());
    }
    @FXML
    private void toggleProfileMenu() {
        boolean isVisible = profileMenu.isVisible();

        profileMenu.setVisible(!isVisible);
        profileMenu.setManaged(!isVisible);
    }


    @FXML
    public void initialize() {
        System.out.println("HomePageController initialisé");

        logoutBtn.setVisible(false);
        logoutBtn.setManaged(false);
        userLabel.setText("");

        // cacher par défaut
        if (badgeBox != null) {
            badgeBox.setVisible(false);
            badgeBox.setManaged(false);
        }
        if (badgeSection != null) {
            badgeSection.setVisible(false);
            badgeSection.setManaged(false);
        }

        if (adminBtn != null) {
            adminBtn.setVisible(true);
            adminBtn.setManaged(true);
        }
    }
    private void refreshScene() {
        if (adminBtn != null && adminBtn.getScene() != null) {
            adminBtn.getScene().getWindow().sizeToScene();
        }
    }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showError("Erreur", "Fichier FXML introuvable : " + fxmlPath);
                return;
            }
            FXMLLoader loader = new FXMLLoader(resource);
            Parent root = loader.load();

            Stage stage = null;
            if (homeBtn != null && homeBtn.getScene() != null) {
                stage = (Stage) homeBtn.getScene().getWindow();
            } else if (coursCard != null && coursCard.getScene() != null) {
                stage = (Stage) coursCard.getScene().getWindow();
            }

            if (stage == null) {
                showError("Erreur", "Impossible de récupérer la fenêtre principale.");
                return;
            }

            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.setMaximized(true);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur de chargement", e.getMessage());
        }
    }


    @FXML
    private void handleHome() {
        System.out.println("Page d'accueil");
    }

    @FXML
    private void handleCours() {
        naviguerVers("/fxml/CoursListFrontView.fxml", "Path2Learn - Cours");
    }

    @FXML
    private void handleRessources() {
        naviguerVers("/fxml/RessourcesViewFront.fxml", "Path2Learn - Ressources");
    }

    @FXML
    private void handleQuestions() {
        naviguerVers("/fxml/QuizList.fxml", "Path2Learn - Quiz");
    }

    @FXML
    private void handleProjets() {
        naviguerVers("/fxml/PortfolioListView.fxml", "Path2Learn - Projets");
    }

    @FXML
    private void handleEvenements() {
        showInfo("Événements", "Page des événements - À venir");
    }

    @FXML
    private void handleUtilisateurs() {
        naviguerVers("/fxml/User.fxml", "Path2Learn - Communauté");
    }
    @FXML

    private void handleLogout() {
        try {
            currentUser = null;

            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/HomePage.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) logoutBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Home");
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur", "Impossible de se déconnecter");
        }
    }
    @FXML
    private void handleLogin() {
        naviguerVers("/fxml/Login.fxml", "Path2Learn - Connexion");
    }

    @FXML
    private void handleRegister() {
        naviguerVers("/fxml/register.fxml", "Inscription");
    }
    @FXML
    private void handleGoToAdmin() {
        naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Administration");
    }

    private void showInfo(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void showError(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    @FXML
    private void handleEditProfile() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/EditUser.fxml"));
            Parent root = loader.load();

            EditUserController controller = loader.getController();
            controller.setUser(currentUser); // 👈 IMPORTANT

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Modifier profil");
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Erreur", "Impossible d'ouvrir le profil");
        }
    }
    private void loadTeacherBadge() {
        try {
            if (currentUser == null) return;

            if (!currentUser.getRole().equalsIgnoreCase("teacher")) {
                // cacher badge box header
                if (badgeBox != null) {
                    badgeBox.setVisible(false);
                    badgeBox.setManaged(false);
                }
                // cacher badge section complète
                if (badgeSection != null) {
                    badgeSection.setVisible(false);
                    badgeSection.setManaged(false);
                }
                return;
            }

            int courseCount = serviceBadge.getCourseCount(userId);
            int points      = courseCount * 10;
            Badge current   = serviceBadge.getCurrentBadge(userId);
            Badge next      = serviceBadge.getNextBadge(userId);

            // ── HEADER MINI BADGE (badgeBox) ──────────────────
            if (badgeLabel != null)
                badgeLabel.setText(current != null
                        ? current.getIcon() + " " + current.getName()
                        : "📚 Aucun badge");

            if (progressLabel != null)
                progressLabel.setText(next != null
                        ? points + " pts • " + (next.getRequired_courses() - courseCount) + " cours restants"
                        : points + " pts • 👑 MAX");

            // ── SECTION BADGE COMPLÈTE ────────────────────────
            if (pointsLabel != null)
                pointsLabel.setText("💎 " + points + " pts • " + courseCount + " cours");

            if (currentBadgeLabel != null)
                currentBadgeLabel.setText(current != null
                        ? current.getIcon() + " " + current.getName()
                        : "📚 Pas encore de badge");

            if (next != null) {
                if (nextBadgeLabel != null)
                    nextBadgeLabel.setText("→ " + next.getName()
                            + " dans " + (next.getRequired_courses() - courseCount) + " cours");
                if (badgeProgress != null) {
                    int from    = current != null ? current.getRequired_courses() : 0;
                    int to      = next.getRequired_courses();
                    double prog = (double)(courseCount - from) / (to - from);
                    badgeProgress.setProgress(Math.max(0, Math.min(1, prog)));
                }
            } else {
                if (nextBadgeLabel != null) nextBadgeLabel.setText("👑 MAX atteint !");
                if (badgeProgress != null)  badgeProgress.setProgress(1.0);
            }

            // afficher les deux sections
            if (badgeBox != null) {
                badgeBox.setVisible(true);
                badgeBox.setManaged(true);
            }
            if (badgeSection != null) {
                badgeSection.setVisible(true);
                badgeSection.setManaged(true);
            }

            loadBadgeShowcase(courseCount, current);

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void loadBadgeShowcase(int courseCount, Badge current) {
        try {
            if (badgeShowcaseBox == null) return;
            badgeShowcaseBox.getChildren().clear();

            List<Badge> allBadges = serviceBadge.recuperer();

            for (Badge b : allBadges) {
                boolean earned    = courseCount >= b.getRequired_courses();
                boolean isCurrent = current != null && current.getId() == b.getId();

                VBox card = new VBox(6);
                card.setAlignment(javafx.geometry.Pos.CENTER);
                card.setPadding(new javafx.geometry.Insets(14));
                card.setPrefWidth(115);

                if (isCurrent) {
                    card.setStyle(
                            "-fx-background-color: #fff8e1;" +
                                    "-fx-border-color: #f9a825;" +
                                    "-fx-border-width: 2;" +
                                    "-fx-border-radius: 12;" +
                                    "-fx-background-radius: 12;" +
                                    "-fx-effect: dropshadow(gaussian, rgba(249,168,37,0.4), 10, 0, 0, 3);"
                    );
                } else if (earned) {
                    card.setStyle(
                            "-fx-background-color: #e8f5e9;" +
                                    "-fx-border-color: #66bb6a;" +
                                    "-fx-border-width: 1.5;" +
                                    "-fx-border-radius: 12;" +
                                    "-fx-background-radius: 12;"
                    );
                } else {
                    card.setStyle(
                            "-fx-background-color: #f5f5f5;" +
                                    "-fx-border-color: #e0e0e0;" +
                                    "-fx-border-width: 1;" +
                                    "-fx-border-radius: 12;" +
                                    "-fx-background-radius: 12;" +
                                    "-fx-opacity: 0.55;"
                    );
                }

                Label icon = new Label(b.getIcon() != null ? b.getIcon() : "🏅");
                icon.setStyle("-fx-font-size: 30px;");

                Label name = new Label(b.getName());
                name.setStyle("-fx-font-size: 12px; -fx-font-weight: bold; -fx-text-fill: #333;");
                name.setWrapText(true);
                name.setTextAlignment(javafx.scene.text.TextAlignment.CENTER);
                name.setMaxWidth(100);

                Label req = new Label(b.getRequired_courses() + " cours requis");
                req.setStyle("-fx-font-size: 10px; -fx-text-fill: #999;");

                String statusText = isCurrent ? "✨ Actuel"
                        : earned  ? "✔ Obtenu"
                        : "🔒 " + (b.getRequired_courses() - courseCount) + " restants";
                String statusColor = isCurrent ? "#f9a825"
                        : earned  ? "#66bb6a"
                        : "#bbb";

                Label status = new Label(statusText);
                status.setStyle("-fx-font-size: 10px; -fx-font-weight: bold; -fx-text-fill: " + statusColor + ";");

                card.getChildren().addAll(icon, name, req, status);
                badgeShowcaseBox.getChildren().add(card);
            }

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}