package org.example;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

public class Path2LearnApp extends Application {

    @Override
    public void start(Stage primaryStage) throws Exception {
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/HomePage.fxml"));
        Parent root = loader.load();

        Scene scene = new Scene(root);

        primaryStage.setTitle("Path2Learn - Plateforme d'apprentissage");
        primaryStage.setScene(scene);

        // Taille normale de fenêtre - NI PLEIN ÉCRAN
        primaryStage.setWidth(1280);
        primaryStage.setHeight(800);

        // Centrer la fenêtre sur l'écran
        primaryStage.centerOnScreen();

        // Empêcher le redimensionnement trop petit
        primaryStage.setMinWidth(1024);
        primaryStage.setMinHeight(700);

        // NE PAS mettre full screen
        // primaryStage.setFullScreen(true);  ← À NE PAS FAIRE

        primaryStage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}