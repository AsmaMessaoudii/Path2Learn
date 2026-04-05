package org.example.Models;

public class Choix {
    private int id;
    private String contenu;
    private boolean estCorrect;
    private int questionId;

    // Constructeur vide
    public Choix() {}

    // Constructeur SANS id (ajout)
    public Choix(String contenu, boolean estCorrect, int questionId) {
        this.contenu = contenu;
        this.estCorrect = estCorrect;
        this.questionId = questionId;
    }

    // Constructeur AVEC id (modifier/supprimer)
    public Choix(int id, String contenu, boolean estCorrect, int questionId) {
        this.id = id;
        this.contenu = contenu;
        this.estCorrect = estCorrect;
        this.questionId = questionId;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getContenu() { return contenu; }
    public void setContenu(String contenu) { this.contenu = contenu; }

    public boolean isEstCorrect() { return estCorrect; }
    public void setEstCorrect(boolean estCorrect) { this.estCorrect = estCorrect; }

    public int getQuestionId() { return questionId; }
    public void setQuestionId(int questionId) { this.questionId = questionId; }

    @Override
    public String toString() {
        return "Choix{" +
                "id=" + id +
                ", contenu='" + contenu + '\'' +
                ", estCorrect=" + estCorrect +
                ", questionId=" + questionId +
                "}\n";
    }
}