package org.example.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.collections.transformation.FilteredList;
import javafx.collections.transformation.SortedList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.stage.Modality;
import javafx.stage.Stage;
import org.example.Models.Cours;
import org.example.Services.ServiceCours;

import java.io.IOException;
import java.sql.Date;
import java.util.List;
import java.util.Optional;

public class CoursController {

    @FXML private TableView<Cours> tableView;
    @FXML private TableColumn<Cours, Integer> colId;
    @FXML private TableColumn<Cours, String> colTitre;
    @FXML private TableColumn<Cours, String> colDescription;
    @FXML private TableColumn<Cours, String> colNiveau;
    @FXML private TableColumn<Cours, String> colMatiere;
    @FXML private TableColumn<Cours, Integer> colDuree;
    @FXML private TableColumn<Cours, Date> colDate;
    @FXML private TableColumn<Cours, String> colStatut;
    @FXML private TableColumn<Cours, Void> colActions;

    @FXML private TextField searchField;
    @FXML private Label statsLabel;
    @FXML private ComboBox<String> matiereFilterCombo;

    // Boutons de navigation
    @FXML private Button homeBtn, coursBtn, ressourcesBtn, questionsBtn, projetsBtn, evenementsBtn, utilisateursBtn;

    private ServiceCours serviceCours;
    private ObservableList<Cours> coursList;
    private FilteredList<Cours> filteredList;
    private List<Cours> allCoursList;

    @FXML
    public void initialize() {
        serviceCours = new ServiceCours();
        setupTableColumns();
        chargerCours();
        setupSearchFilter();
        setupMatiereFilter();
        setupActionButtons();
    }

    // ==================== MÉTHODES DE NAVIGATION ====================

    @FXML
    private void handleHome() {
        naviguerVers("/fxml/MainMenu.fxml", "Path2Learn - Accueil");
    }

    @FXML
    private void handleCours() {
        chargerCours();
    }

    @FXML
    private void handleRessources() {
        naviguerVers("/fxml/RessourcesView.fxml", "Path2Learn - Gestion des ressources");
    }

    @FXML
    private void handleQuestions() {
        showAlert("Quiz", "Module Quiz - Bientôt disponible", Alert.AlertType.INFORMATION);
    }

    @FXML
    private void handleProjets() {
        showAlert("Projets", "Module Projets - Bientôt disponible", Alert.AlertType.INFORMATION);
    }

    @FXML
    private void handleEvenements() {
        showAlert("Événements", "Module Événements - Bientôt disponible", Alert.AlertType.INFORMATION);
    }

    @FXML
    private void handleUtilisateurs() {
        showAlert("Utilisateurs", "Module Utilisateurs - Bientôt disponible", Alert.AlertType.INFORMATION);
    }

    private void naviguerVers(String fxmlPath, String titre) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger la page: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    // ==================== MÉTHODES CRUD ====================

    private void setupTableColumns() {
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colTitre.setCellValueFactory(new PropertyValueFactory<>("titre"));

        // Tronquer la description si trop longue
        colDescription.setCellValueFactory(new PropertyValueFactory<>("description"));
        colDescription.setCellFactory(col -> new TableCell<Cours, String>() {
            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                } else {
                    String text = item.length() > 100 ? item.substring(0, 100) + "..." : item;
                    setText(text);
                }
            }
        });

        colNiveau.setCellValueFactory(new PropertyValueFactory<>("niveau"));
        // Ajouter un style pour le niveau
        colNiveau.setCellFactory(col -> new TableCell<Cours, String>() {
            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                    setStyle("");
                } else {
                    setText(item);
                    switch (item) {
                        case "Débutant":
                            setStyle("-fx-text-fill: #4CAF50; -fx-font-weight: bold;");
                            break;
                        case "Intermédiaire":
                            setStyle("-fx-text-fill: #FF9800; -fx-font-weight: bold;");
                            break;
                        case "Avancé":
                            setStyle("-fx-text-fill: #f44336; -fx-font-weight: bold;");
                            break;
                        default:
                            setStyle("");
                    }
                }
            }
        });

        colMatiere.setCellValueFactory(new PropertyValueFactory<>("matiere"));
        colDuree.setCellValueFactory(new PropertyValueFactory<>("duree"));
        colDate.setCellValueFactory(new PropertyValueFactory<>("date_creation"));

        colStatut.setCellValueFactory(new PropertyValueFactory<>("statut"));
        colStatut.setCellFactory(col -> new TableCell<Cours, String>() {
            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || item == null) {
                    setText(null);
                    setStyle("");
                } else {
                    setText(item);
                    if (item.equals("Publié")) {
                        setStyle("-fx-text-fill: #4CAF50; -fx-font-weight: bold;");
                    } else if (item.equals("Brouillon")) {
                        setStyle("-fx-text-fill: #FF9800; -fx-font-weight: bold;");
                    } else {
                        setStyle("-fx-text-fill: #9E9E9E;");
                    }
                }
            }
        });
    }

    private void chargerCours() {
        try {
            allCoursList = serviceCours.recuperer();
            coursList = FXCollections.observableArrayList(allCoursList);
            filteredList = new FilteredList<>(coursList, p -> true);

            // Initialiser le filtre par matière
            setupMatiereFilterCombo();

            SortedList<Cours> sortedList = new SortedList<>(filteredList);
            sortedList.comparatorProperty().bind(tableView.comparatorProperty());
            tableView.setItems(sortedList);
            updateStats();
        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les cours: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void setupMatiereFilterCombo() {
        matiereFilterCombo.getItems().clear();
        matiereFilterCombo.getItems().add("Toutes les matières");
        if (allCoursList != null) {
            allCoursList.stream()
                    .map(Cours::getMatiere)
                    .distinct()
                    .sorted()
                    .forEach(matiere -> matiereFilterCombo.getItems().add(matiere));
        }
        matiereFilterCombo.setValue("Toutes les matières");
    }

    private void setupSearchFilter() {
        searchField.textProperty().addListener((observable, oldValue, newValue) -> {
            filteredList.setPredicate(cours -> {
                if (newValue == null || newValue.isEmpty()) {
                    return true;
                }
                String lowerCaseFilter = newValue.toLowerCase();
                return cours.getTitre().toLowerCase().contains(lowerCaseFilter) ||
                        cours.getNiveau().toLowerCase().contains(lowerCaseFilter) ||
                        cours.getMatiere().toLowerCase().contains(lowerCaseFilter) ||
                        cours.getDescription().toLowerCase().contains(lowerCaseFilter);
            });
            updateStats();
        });
    }

    private void setupMatiereFilter() {
        matiereFilterCombo.valueProperty().addListener((observable, oldValue, newValue) -> {
            if (newValue == null || newValue.equals("Toutes les matières")) {
                filteredList.setPredicate(cours -> true);
            } else {
                filteredList.setPredicate(cours -> cours.getMatiere().equals(newValue));
            }
            updateStats();
        });
    }

    private void setupActionButtons() {
        colActions.setCellFactory(col -> new TableCell<Cours, Void>() {
            private final Button editBtn = new Button("✏️ Modifier");
            private final Button deleteBtn = new Button("🗑️ Supprimer");
            private final Button ressourcesBtn = new Button("📎 Voir ressources");
            private final HBox buttons = new HBox(8, editBtn, deleteBtn, ressourcesBtn);

            {
                editBtn.setStyle("-fx-background-color: #2196F3; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                deleteBtn.setStyle("-fx-background-color: #f44336; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                ressourcesBtn.setStyle("-fx-background-color: #81C784; -fx-text-fill: white; -fx-font-size: 11px; -fx-padding: 5 10; -fx-background-radius: 5; -fx-cursor: hand;");
                buttons.setAlignment(Pos.CENTER);
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    Cours cours = getTableView().getItems().get(getIndex());
                    editBtn.setOnAction(e -> handleModifier(cours));
                    deleteBtn.setOnAction(e -> handleSupprimer(cours));
                    ressourcesBtn.setOnAction(e -> voirRessourcesParCours(cours));
                    setGraphic(buttons);
                }
            }
        });
    }

    private void voirRessourcesParCours(Cours cours) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/RessourcesView.fxml"));
            Parent root = loader.load();

            RessourcesController ressourcesController = loader.getController();
            ressourcesController.setCoursIdFiltre(cours.getId(), cours.getTitre());

            Stage stage = (Stage) homeBtn.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("Path2Learn - Ressources du cours: " + cours.getTitre());
            stage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible de charger les ressources: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleAjouter() {
        ouvrirDialog(null);
    }

    private void handleModifier(Cours cours) {
        ouvrirDialog(cours);
    }

    private void handleSupprimer(Cours cours) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setHeaderText("Supprimer le cours");
        alert.setContentText("Voulez-vous vraiment supprimer le cours: " + cours.getTitre() + " ?");

        Optional<ButtonType> result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                serviceCours.supprimer(cours);
                chargerCours();
                showAlert("Succès", "Cours supprimé avec succès !", Alert.AlertType.INFORMATION);
            } catch (Exception e) {
                showAlert("Erreur", "Erreur lors de la suppression: " + e.getMessage(), Alert.AlertType.ERROR);
            }
        }
    }

    private void ouvrirDialog(Cours cours) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/CoursDialog.fxml"));
            VBox dialogVBox = loader.load();

            CoursDialogController dialogController = loader.getController();
            dialogController.setServiceCours(serviceCours);
            dialogController.setCours(cours);
            dialogController.setParentController(this);

            Stage dialogStage = new Stage();
            dialogStage.initModality(Modality.APPLICATION_MODAL);
            dialogStage.setTitle(cours == null ? "Ajouter un cours" : "Modifier un cours");
            dialogStage.setScene(new Scene(dialogVBox));
            dialogStage.showAndWait();

        } catch (Exception e) {
            e.printStackTrace();
            showAlert("Erreur", "Impossible d'ouvrir le dialogue: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleActualiser() {
        chargerCours();
        searchField.clear();
        matiereFilterCombo.setValue("Toutes les matières");
        showAlert("Info", "Liste des cours actualisée !", Alert.AlertType.INFORMATION);
    }

    @FXML
    private void handleRechercher() {
        updateStats();
    }

    @FXML
    private void handleResetRecherche() {
        searchField.clear();
        matiereFilterCombo.setValue("Toutes les matières");
        chargerCours();
    }

    private void updateStats() {
        int total = filteredList.size();
        statsLabel.setText(total + " cours trouvé" + (total > 1 ? "s" : ""));
    }

    public void refreshTable() {
        chargerCours();
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}