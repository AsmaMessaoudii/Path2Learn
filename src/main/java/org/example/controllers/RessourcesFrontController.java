package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;
import org.example.Models.Cours;
import org.example.Models.RessourcePedagogique;
import org.example.Services.ServiceCours;
import org.example.Services.ServiceRessourcePedagogique;

import java.awt.Desktop;
import java.io.File;
import java.io.IOException;
import java.net.URI;
import java.util.List;
import java.util.stream.Collectors;

public class RessourcesFrontController {

    // ===== FXML INJECTIONS =====
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;
    @FXML private TextField searchField;
    @FXML private ComboBox<String> typeFilter;
    @FXML private VBox ressourcesContainer;
    @FXML private VBox emptyState;
    @FXML private Label statsRessources;
    @FXML private Label breadcrumbCours;

    // Infos cours
    @FXML private Label coursTitreLabel;
    @FXML private Label coursNiveauLabel;
    @FXML private Label coursMatiereLabel;
    @FXML private Label coursDureeLabel;
    @FXML private Label coursDescLabel;
    @FXML private Label coursEmoji;

    // ===== STATE =====
    private ServiceRessourcePedagogique serviceRessource;
    private ServiceCours serviceCours;
    private List<RessourcePedagogique> allRessources;
    private Cours coursActuel;

    @FXML
    public void initialize() {
        serviceRessource = new ServiceRessourcePedagogique();
        serviceCours     = new ServiceCours();
        setupTypeFilter();

        // Par défaut (accès via menu), charger sans cours
        chargerToutesRessources();
    }

    // ===== MÉTHODE PRINCIPALE POUR DÉFINIR LE COURS =====
    public void setCours(Cours cours) {
        this.coursActuel = cours;
        afficherInfoCours(cours);
        chargerRessources(cours.getId());
    }

    private void setupTypeFilter() {
        typeFilter.setItems(FXCollections.observableArrayList(
                "Tous les types", "PDF", "Vidéo", "Image", "Audio", "Document", "Lien"
        ));
        typeFilter.setValue("Tous les types");
        typeFilter.valueProperty().addListener((obs, o, n) -> appliquerFiltres());
    }

    // ==================== CHARGEMENT ====================

    private void afficherInfoCours(Cours cours) {
        breadcrumbCours.setText(cours.getTitre());
        coursTitreLabel.setText(cours.getTitre());

        String niveau  = cours.getNiveau() != null ? cours.getNiveau() : "—";
        String matiere = cours.getMatiere() != null ? cours.getMatiere() : "—";
        String desc    = cours.getDescription() != null ? cours.getDescription() : "Aucune description disponible.";
        int    duree   = cours.getDuree();

        coursNiveauLabel.setText(niveau);
        coursNiveauLabel.setStyle(getNiveauStyle(niveau));
        coursMatiereLabel.setText("📁 " + matiere);
        coursDureeLabel.setText("⏱ " + duree + " heures");
        coursDescLabel.setText(desc);
        coursEmoji.setText(getEmojiMatiere(matiere));
    }

    private void chargerRessources(int coursId) {
        try {
            allRessources = serviceRessource.recuperer().stream()
                    .filter(r -> r.getCours_id() == coursId)
                    .collect(Collectors.toList());
            searchField.textProperty().addListener((obs, o, n) -> appliquerFiltres());
            appliquerFiltres();
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les ressources : " + e.getMessage());
        }
    }

    private void chargerToutesRessources() {
        try {
            allRessources = serviceRessource.recuperer();
            searchField.textProperty().addListener((obs, o, n) -> appliquerFiltres());

            // Adjust appearance for generic view
            breadcrumbCours.setText("Toutes les ressources");
            coursTitreLabel.setText("Ressources Générales");
            coursNiveauLabel.setText("Tous");
            coursMatiereLabel.setText("Divers");
            coursDureeLabel.setText("—");
            coursDescLabel.setText("Vous consultez la bibliothèque complète des ressources.");
            coursEmoji.setText("📚");

            appliquerFiltres();
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les ressources : " + e.getMessage());
        }
    }

    private void appliquerFiltres() {
        if (allRessources == null) return;

        String recherche = searchField.getText() == null ? "" : searchField.getText().toLowerCase();
        String type      = typeFilter.getValue();

        List<RessourcePedagogique> filtrées = allRessources.stream()
                .filter(r -> recherche.isEmpty()
                        || r.getTitre().toLowerCase().contains(recherche)
                        || (r.getType() != null && r.getType().toLowerCase().contains(recherche)))
                .filter(r -> type == null || type.equals("Tous les types")
                        || (r.getType() != null && r.getType().equalsIgnoreCase(type)))
                .collect(Collectors.toList());

        afficherRessources(filtrées);
    }

    private void afficherRessources(List<RessourcePedagogique> liste) {
        ressourcesContainer.getChildren().clear();

        boolean estVide = liste.isEmpty();
        emptyState.setVisible(estVide);
        emptyState.setManaged(estVide);

        int total = liste.size();
        statsRessources.setText(total + " ressource" + (total > 1 ? "s" : ""));

        for (RessourcePedagogique r : liste) {
            ressourcesContainer.getChildren().add(creerCarteRessource(r));
        }
    }

    // ==================== CRÉATION DE CARTE RESSOURCE ====================

    private HBox creerCarteRessource(RessourcePedagogique r) {
        HBox card = new HBox(16);
        card.setAlignment(Pos.CENTER_LEFT);
        card.setPadding(new Insets(16, 20, 16, 20));
        card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 12;" +
                        "-fx-border-color: #EEEEEE;" +
                        "-fx-border-radius: 12;" +
                        "-fx-border-width: 1;" +
                        "-fx-cursor: hand;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.04), 6, 0, 0, 2);"
        );

        // Icône type
        String typeCouleur = getCouleurType(r.getType());
        String typeEmoji   = getEmojiType(r.getType());
        VBox iconeBox = new VBox();
        iconeBox.setAlignment(Pos.CENTER);
        iconeBox.setPrefSize(48, 48);
        iconeBox.setMinSize(48, 48);
        iconeBox.setStyle("-fx-background-color: " + typeCouleur + "22; -fx-background-radius: 10;");

        boolean hasImageThumbnail = false;
        if (r.getType() != null && r.getType().toLowerCase().contains("image")) {
            if (r.getFile_name() != null && !r.getFile_name().isEmpty()) {
                File f = new File(r.getFile_name());
                if (f.exists()) {
                    try {
                        javafx.scene.image.Image img = new javafx.scene.image.Image(f.toURI().toString(), 44, 44, true, true);
                        if (!img.isError()) {
                            javafx.scene.image.ImageView imgView = new javafx.scene.image.ImageView(img);
                            imgView.setFitWidth(40);
                            imgView.setFitHeight(40);
                            imgView.setPreserveRatio(true);
                            iconeBox.getChildren().add(imgView);
                            iconeBox.setStyle("-fx-background-color: transparent; -fx-padding: 0;");
                            hasImageThumbnail = true;
                        }
                    } catch (Exception ignored) {}
                }
            }
        }

        if (!hasImageThumbnail) {
            Label icone = new Label(typeEmoji);
            icone.setStyle("-fx-font-size: 22px;");
            iconeBox.getChildren().add(icone);
        }

        // Infos
        VBox infos = new VBox(4);
        HBox.setHgrow(infos, Priority.ALWAYS);

        Label titreLabel = new Label(r.getTitre());
        titreLabel.setStyle("-fx-font-size: 14px; -fx-font-weight: bold; -fx-text-fill: #1A1A1A;");

        HBox metaLine = new HBox(10);
        metaLine.setAlignment(Pos.CENTER_LEFT);

        Label typeLabel = new Label(r.getType() != null ? r.getType() : "—");
        typeLabel.setStyle("-fx-background-color: " + typeCouleur + "33; -fx-text-fill: " + typeCouleur + "; -fx-font-size: 10px; -fx-padding: 2 10; -fx-background-radius: 12; -fx-font-weight: bold;");

        String dateStr = r.getDate_ajout() != null ? "Ajouté le " + r.getDate_ajout().toString() : "";
        Label dateLabel = new Label(dateStr);
        dateLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #AAAAAA;");

        metaLine.getChildren().addAll(typeLabel, dateLabel);

        // URL si présent
        if (r.getUrl() != null && !r.getUrl().isEmpty()) {
            Label urlLabel = new Label("🔗 " + r.getUrl());
            urlLabel.setStyle("-fx-font-size: 11px; -fx-text-fill: #42A5F5; -fx-underline: true; -fx-cursor: hand;");
            urlLabel.setOnMouseClicked(e -> {
                try { Desktop.getDesktop().browse(new URI(r.getUrl())); }
                catch (Exception ex) { showAlert("Erreur", "Impossible d'ouvrir le lien."); }
            });
            infos.getChildren().addAll(titreLabel, metaLine, urlLabel);
        } else {
            infos.getChildren().addAll(titreLabel, metaLine);
        }

        // Bouton ouvrir
        Button btnOuvrir = new Button("Ouvrir →");
        btnOuvrir.setStyle(
                "-fx-background-color: " + typeCouleur + ";" +
                        "-fx-text-fill: white;" +
                        "-fx-font-size: 12px;" +
                        "-fx-font-weight: bold;" +
                        "-fx-padding: 8 18;" +
                        "-fx-background-radius: 20;" +
                        "-fx-cursor: hand;"
        );
        btnOuvrir.setOnAction(e -> ouvrirRessource(r));

        // Effet survol
        card.setOnMouseEntered(e -> card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 12;" +
                        "-fx-border-color: " + typeCouleur + ";" +
                        "-fx-border-radius: 12;" +
                        "-fx-border-width: 1.5;" +
                        "-fx-cursor: hand;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.10), 12, 0, 0, 4);"
        ));
        card.setOnMouseExited(e -> card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 12;" +
                        "-fx-border-color: #EEEEEE;" +
                        "-fx-border-radius: 12;" +
                        "-fx-border-width: 1;" +
                        "-fx-cursor: hand;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.04), 6, 0, 0, 2);"
        ));

        card.getChildren().addAll(iconeBox, infos, btnOuvrir);
        return card;
    }

    private void ouvrirRessource(RessourcePedagogique r) {
        // Priorité : URL
        if (r.getUrl() != null && !r.getUrl().isEmpty()) {
            try { Desktop.getDesktop().browse(new URI(r.getUrl())); return; }
            catch (Exception e) { showAlert("Erreur", "Impossible d'ouvrir le lien : " + e.getMessage()); return; }
        }
        // Sinon : fichier local
        if (r.getFile_name() != null && !r.getFile_name().isEmpty()) {
            File f = new File(r.getFile_name());
            if (f.exists()) {
                try { Desktop.getDesktop().open(f); }
                catch (IOException e) { showAlert("Erreur", "Impossible d'ouvrir le fichier : " + e.getMessage()); }
            } else {
                showAlert("Fichier introuvable", "Le fichier n'existe plus à l'emplacement enregistré.");
            }
        } else {
            showAlert("Aucune ressource", "Cette ressource ne possède ni URL ni fichier associé.");
        }
    }

    // ==================== NAVIGATION SIDEBAR ====================

    @FXML private void handleRetourCours() {
        naviguerVers("/fxml/CoursListFrontView.fxml", "Path2Learn — Cours");
    }

    @FXML private void handleHome()         { naviguerVers("/fxml/HomePage.fxml",             "Path2Learn - Accueil"); }
    @FXML private void handleCours()        { naviguerVers("/fxml/CoursListFrontView.fxml",   "Path2Learn — Cours"); }
    @FXML private void handleRessources()   { /* déjà sur la page */ }
    @FXML private void handleQuestions()    { naviguerVers("/fxml/QuizList.fxml",     "Path2Learn - Quiz"); }
    @FXML private void handleProjets()      { naviguerVers("/fxml/PortfolioListView.fxml",    "Path2Learn - Projets"); }
    @FXML private void handleEvenements()   { showAlert("Bientôt disponible", "Le module Événements arrive très bientôt !"); }
    @FXML private void handleUtilisateurs() { naviguerVers("/fxml/User.fxml",                 "Path2Learn - Communauté"); }

    @FXML
    private void handleResetRecherche() {
        searchField.clear();
        typeFilter.setValue("Tous les types");
    }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            java.net.URL resource = getClass().getResource(fxmlPath);
            if (resource == null) {
                showAlert("Erreur", "Page non trouvée : " + fxmlPath);
                return;
            }
            FXMLLoader loader = new FXMLLoader(resource);
            Parent root = loader.load();
            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.setMaximized(true);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger : " + e.getMessage());
        }
    }

    // ==================== HELPERS VISUELS ====================

    private String getCouleurType(String type) {
        if (type == null) return "#90A4AE";
        switch (type.toLowerCase()) {
            case "pdf":      return "#E53935";
            case "vidéo":
            case "video":    return "#1E88E5";
            case "image":    return "#00ACC1";
            case "audio":    return "#8E24AA";
            case "document": return "#43A047";
            case "lien":     return "#FB8C00";
            default:         return "#78909C";
        }
    }

    private String getEmojiType(String type) {
        if (type == null) return "📁";
        switch (type.toLowerCase()) {
            case "pdf":      return "📄";
            case "vidéo":
            case "video":    return "🎬";
            case "image":    return "🖼️";
            case "audio":    return "🎵";
            case "document": return "📝";
            case "lien":     return "🔗";
            default:         return "📁";
        }
    }

    private String getEmojiMatiere(String matiere) {
        if (matiere == null) return "📖";
        switch (matiere.toLowerCase()) {
            case "informatique":   return "💻";
            case "mathématiques":
            case "mathematiques":  return "📐";
            case "design":         return "🎨";
            case "physique":       return "⚛️";
            case "chimie":         return "🧪";
            case "langues":        return "🌍";
            case "histoire":       return "📜";
            default:               return "📖";
        }
    }

    private String getNiveauStyle(String niveau) {
        if (niveau == null) return "-fx-font-size: 11px; -fx-background-color: #EEE; -fx-text-fill: #666; -fx-padding: 3 10; -fx-background-radius: 12;";
        switch (niveau) {
            case "Débutant":      return "-fx-font-size: 11px; -fx-background-color: #E8F5E9; -fx-text-fill: #2E7D32; -fx-padding: 3 10; -fx-background-radius: 12; -fx-font-weight: bold;";
            case "Intermédiaire": return "-fx-font-size: 11px; -fx-background-color: #FFF3E0; -fx-text-fill: #E65100; -fx-padding: 3 10; -fx-background-radius: 12; -fx-font-weight: bold;";
            case "Avancé":        return "-fx-font-size: 11px; -fx-background-color: #FCE4EC; -fx-text-fill: #C62828; -fx-padding: 3 10; -fx-background-radius: 12; -fx-font-weight: bold;";
            default:              return "-fx-font-size: 11px; -fx-background-color: #EEE; -fx-text-fill: #666; -fx-padding: 3 10; -fx-background-radius: 12;";
        }
    }

    private void showAlert(String titre, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(titre);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}