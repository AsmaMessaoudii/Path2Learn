package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
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
import org.example.Services.ServiceCours;

import java.io.IOException;
import java.util.List;
import java.util.stream.Collectors;

public class CoursListFrontController {

    // ===== FXML INJECTIONS =====
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;
    @FXML private TextField searchField;
    @FXML private ComboBox<String> niveauFilter;
    @FXML private ComboBox<String> matiereFilter;
    @FXML private FlowPane coursFlowPane;
    @FXML private Label statsLabel;
    @FXML private Label userLabel;
    @FXML private VBox emptyState;

    // Chips
    @FXML private Button chipTous, chipPublies, chipBrouillons, chipDebutant, chipIntermediaire, chipAvance;

    // ===== STATE =====
    private ServiceCours serviceCours;
    private List<Cours> allCours;
    private String currentStatutFilter = "tous";

    // ===== STYLES =====
    private static final String CHIP_ACTIVE  = "-fx-background-color: #66BB6A; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 4 14; -fx-background-radius: 20; -fx-cursor: hand;";
    private static final String CHIP_DEFAULT = "-fx-background-color: #F5F5F5; -fx-text-fill: #555; -fx-font-size: 11px; -fx-padding: 4 14; -fx-background-radius: 20; -fx-border-color: #DDD; -fx-cursor: hand;";

    @FXML
    public void initialize() {
        serviceCours = new ServiceCours();
        setupFilters();
        chargerCours();
        setupSearch();
        
        // Hide filter chips since users should only see published courses
        if (chipTous != null) { chipTous.setVisible(false); chipTous.setManaged(false); }
        if (chipPublies != null) { chipPublies.setVisible(false); chipPublies.setManaged(false); }
        if (chipBrouillons != null) { chipBrouillons.setVisible(false); chipBrouillons.setManaged(false); }
    }

    // ==================== CHARGEMENT ====================

    private void chargerCours() {
        try {
            // Uniquement les cours "Publié"
            allCours = serviceCours.recuperer().stream()
                    .filter(c -> "Publié".equals(c.getStatut()))
                    .collect(Collectors.toList());
            populateMatiereFilter();
            appliquerFiltres();
        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les cours : " + e.getMessage());
        }
    }

    private void setupFilters() {
        niveauFilter.setItems(FXCollections.observableArrayList(
                "Tous les niveaux", "Débutant", "Intermédiaire", "Avancé"
        ));
        niveauFilter.setValue("Tous les niveaux");
        niveauFilter.valueProperty().addListener((obs, o, n) -> appliquerFiltres());

        matiereFilter.setValue(null);
        matiereFilter.valueProperty().addListener((obs, o, n) -> appliquerFiltres());
    }

    private void populateMatiereFilter() {
        ObservableList<String> matieres = FXCollections.observableArrayList();
        matieres.add("Toutes les matières");
        allCours.stream().map(Cours::getMatiere).distinct().sorted().forEach(matieres::add);
        matiereFilter.setItems(matieres);
        matiereFilter.setValue("Toutes les matières");
    }

    private void setupSearch() {
        searchField.textProperty().addListener((obs, o, n) -> appliquerFiltres());
    }

    private void appliquerFiltres() {
        if (allCours == null) return;

        String recherche = searchField.getText() == null ? "" : searchField.getText().toLowerCase();
        String niveau    = niveauFilter.getValue();
        String matiere   = matiereFilter.getValue();

        List<Cours> filtrés = allCours.stream()
                .filter(c -> recherche.isEmpty()
                        || c.getTitre().toLowerCase().contains(recherche)
                        || c.getMatiere().toLowerCase().contains(recherche)
                        || c.getNiveau().toLowerCase().contains(recherche)
                        || c.getDescription().toLowerCase().contains(recherche))
                .filter(c -> niveau == null || niveau.equals("Tous les niveaux") || c.getNiveau().equals(niveau))
                .filter(c -> matiere == null || matiere.equals("Toutes les matières") || c.getMatiere().equals(matiere))
                .filter(c -> {
                    switch (currentStatutFilter) {
                        case "publie":    return "Publié".equals(c.getStatut());
                        case "brouillon": return "Brouillon".equals(c.getStatut());
                        case "debutant":  return "Débutant".equals(c.getNiveau());
                        case "intermediaire": return "Intermédiaire".equals(c.getNiveau());
                        case "avance":    return "Avancé".equals(c.getNiveau());
                        default:          return true;
                    }
                })
                .collect(Collectors.toList());

        afficherCours(filtrés);
    }

    private void afficherCours(List<Cours> liste) {
        coursFlowPane.getChildren().clear();

        boolean estVide = liste.isEmpty();
        emptyState.setVisible(estVide);
        emptyState.setManaged(estVide);

        int total = liste.size();
        long publies = liste.stream().filter(c -> "Publié".equals(c.getStatut())).count();
        statsLabel.setText(String.format("(%d cours · %d publiés)", total, publies));

        for (Cours cours : liste) {
            coursFlowPane.getChildren().add(creerCarteCours(cours));
        }
    }

    // ==================== CRÉATION DE CARTE ====================

    private VBox creerCarteCours(Cours cours) {
        VBox card = new VBox(0);
        card.setPrefWidth(260);
        card.setMaxWidth(260);
        card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 14;" +
                        "-fx-border-radius: 14;" +
                        "-fx-border-color: #E8E8E8;" +
                        "-fx-border-width: 1;" +
                        "-fx-cursor: hand;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.06), 8, 0, 0, 3);"
        );

        // Bande de couleur en haut selon matière
        String couleur = getCouleurMatiere(cours.getMatiere());
        HBox bandeau = new HBox();
        bandeau.setPrefHeight(5);
        bandeau.setStyle("-fx-background-color: " + couleur + "; -fx-background-radius: 14 14 0 0;");

        // Corps de la carte
        VBox corps = new VBox(10);
        corps.setPadding(new Insets(16, 18, 14, 18));

        // Ligne 1 : emoji + statut
        HBox ligne1 = new HBox();
        ligne1.setAlignment(Pos.CENTER_LEFT);

        Label emoji = new Label(getEmojiMatiere(cours.getMatiere()));
        emoji.setStyle("-fx-font-size: 26px;");

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Label statut = new Label(cours.getStatut());
        String statutStyle = "Publié".equals(cours.getStatut())
                ? "-fx-background-color: #E8F5E9; -fx-text-fill: #2E7D32; -fx-font-size: 10px; -fx-padding: 3 9; -fx-background-radius: 20; -fx-font-weight: bold;"
                : "-fx-background-color: #FFF3E0; -fx-text-fill: #E65100; -fx-font-size: 10px; -fx-padding: 3 9; -fx-background-radius: 20; -fx-font-weight: bold;";
        statut.setStyle(statutStyle);

        ligne1.getChildren().addAll(emoji, spacer, statut);

        // Titre
        Label titre = new Label(cours.getTitre());
        titre.setWrapText(true);
        titre.setStyle("-fx-font-size: 14px; -fx-font-weight: bold; -fx-text-fill: #1A1A1A;");
        titre.setMaxWidth(220);

        // Description courte
        String desc = cours.getDescription();
        if (desc != null && desc.length() > 85) desc = desc.substring(0, 85) + "…";
        Label description = new Label(desc);
        description.setWrapText(true);
        description.setStyle("-fx-font-size: 11px; -fx-text-fill: #888888;");
        description.setMaxWidth(220);

        // Tags : matière + niveau
        HBox tags = new HBox(6);
        tags.setAlignment(Pos.CENTER_LEFT);

        Label tagMatiere = new Label("📁 " + cours.getMatiere());
        tagMatiere.setStyle("-fx-background-color: #F0F4FF; -fx-text-fill: #3949AB; -fx-font-size: 10px; -fx-padding: 3 9; -fx-background-radius: 12;");

        String niveauCouleur = getNiveauStyle(cours.getNiveau());
        Label tagNiveau = new Label(cours.getNiveau());
        tagNiveau.setStyle(niveauCouleur);

        tags.getChildren().addAll(tagMatiere, tagNiveau);

        // Footer : durée
        HBox footer = new HBox();
        footer.setAlignment(Pos.CENTER_LEFT);
        footer.setStyle("-fx-padding: 8 0 0 0; -fx-border-color: #F0F0F0; -fx-border-width: 1 0 0 0;");

        Label duree = new Label("⏱ " + cours.getDuree() + " heures");
        duree.setStyle("-fx-font-size: 11px; -fx-text-fill: #AAAAAA;");

        Region footerSpacer = new Region();
        HBox.setHgrow(footerSpacer, Priority.ALWAYS);

        Label id = new Label("#" + cours.getId());
        id.setStyle("-fx-font-size: 10px; -fx-text-fill: #CCCCCC;");

        footer.getChildren().addAll(duree, footerSpacer, id);

        // Bouton "Voir les ressources"
        Button btnRessources = new Button("📎  Voir les ressources associées");
        btnRessources.setMaxWidth(Double.MAX_VALUE);
        btnRessources.setStyle(
                "-fx-background-color: #F8F9FA;" +
                        "-fx-text-fill: #555555;" +
                        "-fx-font-size: 12px;" +
                        "-fx-padding: 10 0;" +
                        "-fx-cursor: hand;" +
                        "-fx-background-radius: 0 0 12 12;" +
                        "-fx-border-color: #EEEEEE;" +
                        "-fx-border-width: 1 0 0 0;"
        );
        btnRessources.setOnMouseEntered(e -> btnRessources.setStyle(
                "-fx-background-color: " + couleur + "22;" +
                        "-fx-text-fill: " + couleur + ";" +
                        "-fx-font-size: 12px;" +
                        "-fx-font-weight: bold;" +
                        "-fx-padding: 10 0;" +
                        "-fx-cursor: hand;" +
                        "-fx-background-radius: 0 0 12 12;" +
                        "-fx-border-color: " + couleur + "44;" +
                        "-fx-border-width: 1 0 0 0;"
        ));
        btnRessources.setOnMouseExited(e -> btnRessources.setStyle(
                "-fx-background-color: #F8F9FA;" +
                        "-fx-text-fill: #555555;" +
                        "-fx-font-size: 12px;" +
                        "-fx-padding: 10 0;" +
                        "-fx-cursor: hand;" +
                        "-fx-background-radius: 0 0 12 12;" +
                        "-fx-border-color: #EEEEEE;" +
                        "-fx-border-width: 1 0 0 0;"
        ));
        btnRessources.setOnAction(e -> ouvrirRessourcesDuCours(cours));

        // Effet survol sur la carte
        card.setOnMouseEntered(e -> card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 14;" +
                        "-fx-border-radius: 14;" +
                        "-fx-border-color: " + couleur + ";" +
                        "-fx-border-width: 1.5;" +
                        "-fx-cursor: hand;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.13), 16, 0, 0, 6);"
        ));
        card.setOnMouseExited(e -> card.setStyle(
                "-fx-background-color: white;" +
                        "-fx-background-radius: 14;" +
                        "-fx-border-radius: 14;" +
                        "-fx-border-color: #E8E8E8;" +
                        "-fx-border-width: 1;" +
                        "-fx-cursor: hand;" +
                        "-fx-effect: dropshadow(three-pass-box, rgba(0,0,0,0.06), 8, 0, 0, 3);"
        ));
        card.setOnMouseClicked(e -> ouvrirRessourcesDuCours(cours));

        corps.getChildren().addAll(ligne1, titre, description, tags, footer);
        card.getChildren().addAll(bandeau, corps, btnRessources);
        return card;
    }

    // ==================== NAVIGATION VERS RESSOURCES ====================

    private void ouvrirRessourcesDuCours(Cours cours) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/RessourcesViewFront.fxml"));
            Parent root = loader.load();

            RessourcesFrontController controller = loader.getController();
            controller.setCours(cours);

            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Path2Learn — Ressources : " + cours.getTitre());
            stage.setMaximized(true);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible d'ouvrir les ressources : " + e.getMessage());
        }
    }

    // ==================== FILTRES CHIPS ====================

    @FXML private void filterTous()          { setChipActif(chipTous,          "tous");         }
    @FXML private void filterPublies()       { setChipActif(chipPublies,       "publie");        }
    @FXML private void filterBrouillons()    { setChipActif(chipBrouillons,    "brouillon");     }
    @FXML private void filterDebutant()      { setChipActif(chipDebutant,      "debutant");      }
    @FXML private void filterIntermediaire() { setChipActif(chipIntermediaire, "intermediaire"); }
    @FXML private void filterAvance()        { setChipActif(chipAvance,        "avance");        }

    private void setChipActif(Button chip, String filtre) {
        currentStatutFilter = filtre;
        Button[] chips = { chipTous, chipPublies, chipBrouillons, chipDebutant, chipIntermediaire, chipAvance };
        for (Button c : chips) c.setStyle(CHIP_DEFAULT);
        chip.setStyle(CHIP_ACTIVE);
        appliquerFiltres();
    }

    @FXML
    private void handleReset() {
        searchField.clear();
        niveauFilter.setValue("Tous les niveaux");
        matiereFilter.setValue("Toutes les matières");
        setChipActif(chipTous, "tous");
    }

    // ==================== NAVIGATION SIDEBAR ====================

    @FXML private void handleHome()         { naviguerVers("/fxml/HomePage.fxml",         "Path2Learn - Accueil"); }
    @FXML private void handleCours()        { /* déjà sur la page des cours */ }
    @FXML private void handleRessources()   { naviguerVers("/fxml/RessourcesViewFront.fxml", "Path2Learn - Ressources"); }
    @FXML private void handleQuestions()    { naviguerVers("/fxml/QuestionListView.fxml", "Path2Learn - Quiz"); }
    @FXML private void handleProjets()      { naviguerVers("/fxml/PortfolioListView.fxml","Path2Learn - Projets"); }
    @FXML private void handleEvenements()   { showAlert("Bientôt disponible", "Le module Événements arrive très bientôt !"); }
    @FXML private void handleUtilisateurs() { naviguerVers("/fxml/User.fxml",             "Path2Learn - Communauté"); }

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
            showAlert("Erreur", "Impossible de charger la page : " + e.getMessage());
        }
    }

    // ==================== HELPERS VISUELS ====================

    private String getCouleurMatiere(String matiere) {
        if (matiere == null) return "#90A4AE";
        switch (matiere.toLowerCase()) {
            case "informatique":   return "#42A5F5";
            case "mathématiques":
            case "mathematiques":  return "#AB47BC";
            case "design":         return "#FF7043";
            case "physique":       return "#26A69A";
            case "chimie":         return "#66BB6A";
            case "langues":        return "#FFA726";
            case "histoire":       return "#8D6E63";
            default:               return "#78909C";
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
        if (niveau == null) return "-fx-background-color: #EEEEEE; -fx-text-fill: #666; -fx-font-size: 10px; -fx-padding: 3 9; -fx-background-radius: 12;";
        switch (niveau) {
            case "Débutant":      return "-fx-background-color: #E8F5E9; -fx-text-fill: #2E7D32; -fx-font-size: 10px; -fx-padding: 3 9; -fx-background-radius: 12; -fx-font-weight: bold;";
            case "Intermédiaire": return "-fx-background-color: #FFF3E0; -fx-text-fill: #E65100; -fx-font-size: 10px; -fx-padding: 3 9; -fx-background-radius: 12; -fx-font-weight: bold;";
            case "Avancé":        return "-fx-background-color: #FCE4EC; -fx-text-fill: #C62828; -fx-font-size: 10px; -fx-padding: 3 9; -fx-background-radius: 12; -fx-font-weight: bold;";
            default:              return "-fx-background-color: #EEEEEE; -fx-text-fill: #666; -fx-font-size: 10px; -fx-padding: 3 9; -fx-background-radius: 12;";
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