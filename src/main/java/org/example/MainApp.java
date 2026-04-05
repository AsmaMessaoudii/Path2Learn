package org.example;

import javafx.application.Application;
import javafx.geometry.Insets;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;
import org.example.Views.CoursView;
import org.example.Views.RessourceView;
import org.example.Views.QuestionView;
import org.example.Views.ChoixView;

public class MainApp extends Application {

    @Override
    public void start(Stage stage) {
        TabPane tabPane = new TabPane();
        tabPane.setTabClosingPolicy(TabPane.TabClosingPolicy.UNAVAILABLE);
        tabPane.getStyleClass().add("tab-pane");

        // Création des vues
        CoursView coursView = new CoursView();
        RessourceView ressourceView = new RessourceView();
        QuestionView questionView = new QuestionView();
        ChoixView choixView = new ChoixView();

        Tab tabCours = new Tab("📚 Cours", coursView.getView());
        Tab tabRessource = new Tab("📎 Ressources", ressourceView.getView());
        Tab tabQuestion = new Tab("❓ Questions", questionView.getView());
        Tab tabChoix = new Tab("✅ Choix", choixView.getView());

        tabPane.getTabs().addAll(tabCours, tabRessource, tabQuestion, tabChoix);

        Scene scene = new Scene(tabPane, 1100, 750);

        // Charger le CSS
        scene.getStylesheets().add(getClass().getResource("/styles/application.css").toExternalForm());

        stage.setTitle("🎓 Path2Learn - Plateforme Éducative");
        stage.setScene(scene);
        stage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}