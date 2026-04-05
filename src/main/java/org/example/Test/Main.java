package org.example.Test;

import org.example.Models.Question;
import org.example.Services.QuestionService;
import java.sql.SQLDataException;
import java.util.Date;

public class Main {
    public static void main(String[] args) {

        QuestionService service = new QuestionService();

        try {
            // ➕ Ajouter
            service.ajouter(new Question("Question Java", "Différence JDK et JRE ?", new Date(), 30, 20.0f, 1));
            service.ajouter(new Question("Question SQL", "C'est quoi une clé étrangère ?", new Date(), 20, 15.0f, 1));

            // ✏️ Modifier (avec id)
            service.modifier(new Question(120, "Question modifiée", "Nouvelle description", new Date(), 45, 18.0f, 1));

            // 📋 Afficher
            System.out.println(service.recuperer());

            // 🗑️ Supprimer
            Question aSupprimer = new Question();
            aSupprimer.setId(122);
            service.supprimer(aSupprimer);

        } catch (SQLDataException e) {
            throw new RuntimeException(e);
        }
    }
}