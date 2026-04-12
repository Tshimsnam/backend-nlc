import { useState, useEffect } from "react";

// ─── Types ────────────────────────────────────────────────────────────────────
type Answer = "vrai" | "faux" | "peut_etre";
type Answers = Record<number, Answer>;

interface Question {
  id: number;
  text: string;
}

// ─── Questions du quiz ────────────────────────────────────────────────────────
const QUESTIONS: Question[] = [
  { id: 1,  text: "Un retrait de l'environnement immédiat ?" },
  { id: 2,  text: "Une maladie physique ?" },
  { id: 3,  text: "Transmis d'une génération à l'autre ?" },
  { id: 4,  text: "Une maladie mentale ?" },
  { id: 5,  text: "Associé à l'épilepsie ?" },
  { id: 6,  text: "Lié à une mauvaise éducation parentale ?" },
  { id: 7,  text: "Observé plus souvent chez les garçons que chez les filles ?" },
  { id: 8,  text: "Diagnostiqué par des analyses de sang ?" },
  { id: 9,  text: "Est-ce curable ?" },
  { id: 10, text: "A un comportement difficile ?" },
  { id: 11, text: "A des difficultés à communiquer ?" },
  { id: 12, text: "Peut mener une vie épanouie avec le bon accompagnement ?" },
];

const QUIZ_SLUG = "gsa-2026";
const TOKEN_KEY = `quiz_token_${QUIZ_SLUG}`;
const DONE_KEY  = `quiz_done_${QUIZ_SLUG}`;
const API_BASE  = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api";

// ─── Composant bouton réponse ─────────────────────────────────────────────────
const AnswerBtn = ({
  label, value, selected, onClick,
}: {
  label: string; value: Answer; selected: boolean; onClick: () => void;
}) => {
  const colors: Record<Answer, string> = {
    vrai:      selected ? "bg-green-500 text-white border-green-500"  : "border-green-300 text-green-700 hover:bg-green-50",
    faux:      selected ? "bg-red-500 text-white border-red-500"      : "border-red-300 text-red-700 hover:bg-red-50",
    peut_etre: selected ? "bg-amber-400 text-white border-amber-400"  : "border-amber-300 text-amber-700 hover:bg-amber-50",
  };
  return (
    <button
      onClick={onClick}
      className={`px-4 py-2 rounded-full border-2 text-sm font-semibold transition-all ${colors[value]}`}
    >
      {label}
    </button>
  );
};

// ─── Page principale ──────────────────────────────────────────────────────────
export default function QuizGSA2026Page() {
  const [answers, setAnswers]     = useState<Answers>({});
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading]     = useState(false);
  const [error, setError]         = useState<string | null>(null);

  // Vérifier si déjà soumis
  useEffect(() => {
    if (localStorage.getItem(DONE_KEY) === "1") setSubmitted(true);
  }, []);

  const handleAnswer = (questionId: number, answer: Answer) => {
    setAnswers((prev) => ({ ...prev, [questionId]: answer }));
  };

  const answered   = Object.keys(answers).length;
  const allAnswered = answered === QUESTIONS.length;

  const handleSubmit = async () => {
    if (!allAnswered) return;
    setLoading(true);
    setError(null);

    // Récupérer ou générer un token anonyme
    let token = localStorage.getItem(TOKEN_KEY);
    if (!token) {
      // Fallback compatible HTTP (pas besoin de HTTPS)
      token = Array.from({ length: 40 }, () =>
        Math.floor(Math.random() * 36).toString(36)
      ).join("");
      localStorage.setItem(TOKEN_KEY, token);
    }

    try {
      const res = await fetch(`${API_BASE}/quiz/submit`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Quiz-Token": token,
        },
        body: JSON.stringify({ quiz_slug: QUIZ_SLUG, answers }),
      });

      const data = await res.json();

      if (!res.ok) {
        setError(data.message ?? "Une erreur est survenue.");
        return;
      }

      // Sauvegarder le token retourné par le serveur
      if (data.token) localStorage.setItem(TOKEN_KEY, data.token);
      localStorage.setItem(DONE_KEY, "1");
      setSubmitted(true);
    } catch {
      setError("Impossible de contacter le serveur. Vérifiez votre connexion.");
    } finally {
      setLoading(false);
    }
  };

  // ── Écran de remerciement ──────────────────────────────────────────────────
  if (submitted) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-purple-50 to-blue-50 flex items-center justify-center p-4">
        <div className="bg-white rounded-3xl shadow-xl p-10 max-w-md w-full text-center">
          <div className="text-6xl mb-4">🎉</div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">Merci pour votre participation !</h2>
          <p className="text-gray-500 text-sm">
            Vos réponses ont été enregistrées de façon anonyme.<br />
            Ensemble, sensibilisons autour de l'autisme.
          </p>
          <div className="mt-6 text-xs text-gray-400">GSA 2026 — Never Limit Children</div>
        </div>
      </div>
    );
  }

  // ── Formulaire quiz ────────────────────────────────────────────────────────
  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 to-blue-50 py-10 px-4">
      <div className="max-w-2xl mx-auto">

        {/* Header */}
        <div className="text-center mb-8">
          <p className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-1">GSA 2026</p>
          <h1 className="text-3xl font-black text-gray-800 mb-2">Quiz sur l'Autisme</h1>
          <p className="text-gray-500 text-sm">
            <span className="font-semibold text-gray-700">L'autisme est…</span>
            &nbsp;Répondez à chaque affirmation.
          </p>
          <div className="mt-3 flex items-center justify-center gap-2 text-xs text-gray-400">
            <span className="w-2 h-2 rounded-full bg-green-400 inline-block"></span> Vrai
            <span className="w-2 h-2 rounded-full bg-red-400 inline-block ml-2"></span> Faux
            <span className="w-2 h-2 rounded-full bg-amber-400 inline-block ml-2"></span> Peut-être
          </div>
        </div>

        {/* Progression */}
        <div className="mb-6">
          <div className="flex justify-between text-xs text-gray-400 mb-1">
            <span>{answered} / {QUESTIONS.length} réponses</span>
            <span>{Math.round((answered / QUESTIONS.length) * 100)} %</span>
          </div>
          <div className="h-2 bg-gray-200 rounded-full overflow-hidden">
            <div
              className="h-2 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full transition-all duration-300"
              style={{ width: `${(answered / QUESTIONS.length) * 100}%` }}
            />
          </div>
        </div>

        {/* Questions */}
        <div className="space-y-4">
          {QUESTIONS.map((q, i) => (
            <div
              key={q.id}
              className={`bg-white rounded-2xl p-5 shadow-sm border-2 transition-all ${
                answers[q.id] ? "border-purple-200" : "border-transparent"
              }`}
            >
              <div className="flex items-start gap-3 mb-4">
                <span className="w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                  {i + 1}
                </span>
                <p className="text-gray-800 font-medium text-sm leading-relaxed">{q.text}</p>
              </div>
              <div className="flex gap-3 flex-wrap pl-10">
                <AnswerBtn label="✅ Vrai"      value="vrai"      selected={answers[q.id] === "vrai"}      onClick={() => handleAnswer(q.id, "vrai")} />
                <AnswerBtn label="❌ Faux"      value="faux"      selected={answers[q.id] === "faux"}      onClick={() => handleAnswer(q.id, "faux")} />
                <AnswerBtn label="🤔 Peut-être" value="peut_etre" selected={answers[q.id] === "peut_etre"} onClick={() => handleAnswer(q.id, "peut_etre")} />
              </div>
            </div>
          ))}
        </div>

        {/* Erreur */}
        {error && (
          <div className="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            {error}
          </div>
        )}

        {/* Bouton soumettre */}
        <div className="mt-8 text-center">
          <button
            onClick={handleSubmit}
            disabled={!allAnswered || loading}
            className={`px-10 py-4 rounded-2xl text-white font-bold text-base transition-all shadow-lg ${
              allAnswered && !loading
                ? "bg-gradient-to-r from-purple-600 to-blue-600 hover:opacity-90 hover:shadow-xl"
                : "bg-gray-300 cursor-not-allowed"
            }`}
          >
            {loading ? "Envoi en cours…" : allAnswered ? "Soumettre mes réponses" : `Répondez à toutes les questions (${QUESTIONS.length - answered} restantes)`}
          </button>
          <p className="text-xs text-gray-400 mt-3">🔒 Réponses anonymes — aucune donnée personnelle collectée</p>
        </div>

        <div className="text-center mt-8 text-xs text-gray-300">Never Limit Children — GSA 2026</div>
      </div>
    </div>
  );
}
