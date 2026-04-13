import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import Header from "@/components/layout/Header";
import { motion, AnimatePresence } from "framer-motion";

type Answer = "vrai" | "faux" | "peut_etre";
type Answers = Record<number, Answer>;
interface Question { id: number; text: string; correct_answer?: string; }

const API_BASE = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api";

const OPTIONS: { label: string; emoji: string; value: Answer; bg: string; border: string; text: string; selectedBg: string; selectedBorder: string }[] = [
  {
    label: "Vrai", emoji: "✅", value: "vrai",
    bg: "bg-green-50 dark:bg-green-900/10", border: "border-green-200 dark:border-green-800", text: "text-green-800 dark:text-green-300",
    selectedBg: "bg-green-500 dark:bg-green-600", selectedBorder: "border-green-500",
  },
  {
    label: "Faux", emoji: "❌", value: "faux",
    bg: "bg-red-50 dark:bg-red-900/10", border: "border-red-200 dark:border-red-800", text: "text-red-800 dark:text-red-300",
    selectedBg: "bg-red-500 dark:bg-red-600", selectedBorder: "border-red-500",
  },
  {
    label: "Peut-être", emoji: "🤔", value: "peut_etre",
    bg: "bg-amber-50 dark:bg-amber-900/10", border: "border-amber-200 dark:border-amber-800", text: "text-amber-800 dark:text-amber-300",
    selectedBg: "bg-amber-400 dark:bg-amber-500", selectedBorder: "border-amber-400",
  },
];

export default function QuizGSA2026Page() {
  const { slug } = useParams();
  const navigate  = useNavigate();

  const QUIZ_SLUG = slug ?? "gsa-2026";
  const TOKEN_KEY = `quiz_token_${QUIZ_SLUG}`;
  const DONE_KEY  = `quiz_done_${QUIZ_SLUG}`;
  const returnUrl = slug ? `/evenements/${slug}?done=true#test` : "/evenements";

  const [questions, setQuestions]   = useState<Question[]>([]);
  const [loadingQ, setLoadingQ]     = useState(true);
  const [current, setCurrent]       = useState(0);
  const [direction, setDirection]   = useState(1);
  const [answers, setAnswers]       = useState<Answers>({});
  const [submitted, setSubmitted]   = useState(false);
  const [loading, setLoading]       = useState(false);
  const [error, setError]           = useState<string | null>(null);
  const [justAnswered, setJustAnswered] = useState(false);

  useEffect(() => {
    if (localStorage.getItem(DONE_KEY) === "1") {
      setSubmitted(true);
      setLoadingQ(false);
      return;
    }
    // Utilise la route par slug si disponible, sinon fallback sur quiz_slug
    const url = slug
      ? `${API_BASE}/evenements/${slug}/quiz/questions`
      : `${API_BASE}/quiz/questions?quiz_slug=${QUIZ_SLUG}`;
    fetch(url)
      .then(r => r.json())
      .then(data => setQuestions(data.questions ?? []))
      .catch(() => setError("Impossible de charger les questions."))
      .finally(() => setLoadingQ(false));
  }, [DONE_KEY, QUIZ_SLUG, slug]);

  const question    = questions[current];
  const answered    = Object.keys(answers).length;
  const isLast      = current === questions.length - 1;
  const allAnswered = questions.length > 0 && answered === questions.length;
  const progress    = questions.length > 0 ? (answered / questions.length) * 100 : 0;

  const goTo = (index: number) => {
    setDirection(index > current ? 1 : -1);
    setJustAnswered(false);
    setCurrent(index);
  };

  const handleAnswer = (value: Answer) => {
    if (!question) return;
    setAnswers(prev => ({ ...prev, [question.id]: value }));
    setJustAnswered(true);
    if (!isLast) {
      setTimeout(() => {
        setDirection(1);
        setJustAnswered(false);
        setCurrent(c => c + 1);
      }, 600);
    }
  };

  const handleSubmit = async () => {
    if (!allAnswered) return;
    setLoading(true); setError(null);
    let token = localStorage.getItem(TOKEN_KEY);
    if (!token) {
      token = Array.from({ length: 40 }, () => Math.floor(Math.random() * 36).toString(36)).join("");
      localStorage.setItem(TOKEN_KEY, token);
    }
    try {
      const res = await fetch(
        slug ? `${API_BASE}/evenements/${slug}/quiz/submit` : `${API_BASE}/quiz/submit`,
        {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-Quiz-Token": token },
          body: JSON.stringify({ quiz_slug: QUIZ_SLUG, answers }),
        }
      );
      const data = await res.json();
      if (!res.ok) { setError(data.message ?? "Une erreur est survenue."); return; }
      if (data.token) localStorage.setItem(TOKEN_KEY, data.token);
      localStorage.setItem(DONE_KEY, "1");
      setSubmitted(true);
    } catch {
      setError("Impossible de contacter le serveur.");
    } finally {
      setLoading(false);
    }
  };

  // ── Remerciement ────────────────────────────────────────────────────────────
  if (submitted) {
    return (
      <div className="min-h-screen bg-background">
        <Header />
        <div className="min-h-screen flex items-center justify-center p-4 pt-28">
          <motion.div
            initial={{ scale: 0.8, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ type: "spring", stiffness: 200, damping: 20 }}
            className="bg-card rounded-3xl shadow-xl border border-border p-10 max-w-md w-full text-center"
          >
            <motion.div initial={{ scale: 0 }} animate={{ scale: 1 }} transition={{ delay: 0.2, type: "spring", stiffness: 300 }} className="text-7xl mb-6">
              🎉
            </motion.div>
            <h2 className="text-2xl font-bold text-foreground mb-3">Merci pour votre participation !</h2>
            <p className="text-muted-foreground text-sm mb-8 leading-relaxed">
              Vos réponses ont été enregistrées de façon anonyme.<br />
              Ensemble, sensibilisons autour de l'autisme.
            </p>
            <motion.button whileHover={{ scale: 1.03 }} whileTap={{ scale: 0.97 }}
              onClick={() => navigate(returnUrl)}
              className="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 text-white font-semibold text-sm hover:opacity-90 transition-all shadow-md w-full">
              ← Retour à l'événement
            </motion.button>
            <p className="mt-6 text-xs text-muted-foreground">GSA 2026 — Never Limit Children</p>
          </motion.div>
        </div>
      </div>
    );
  }

  // ── Chargement ──────────────────────────────────────────────────────────────
  if (loadingQ) {
    return (
      <div className="min-h-screen bg-background">
        <Header />
        <div className="min-h-screen flex items-center justify-center pt-28">
          <motion.div animate={{ rotate: 360 }} transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
            className="w-10 h-10 border-4 border-purple-500 border-t-transparent rounded-full" />
        </div>
      </div>
    );
  }

  // ── Erreur chargement ───────────────────────────────────────────────────────
  if (error && questions.length === 0) {
    return (
      <div className="min-h-screen bg-background">
        <Header />
        <div className="min-h-screen flex flex-col items-center justify-center pt-28 gap-4">
          <p className="text-red-500 text-sm">{error}</p>
          <button onClick={() => navigate(returnUrl)} className="text-sm text-purple-600 hover:underline">← Retour à l'événement</button>
        </div>
      </div>
    );
  }

  // ── Quiz ────────────────────────────────────────────────────────────────────
  return (
    <div className="min-h-screen bg-secondary">
      <Header />
      <div className="pt-28 pb-16 px-4">
        <div className="max-w-lg mx-auto">

          {/* Header */}
          <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} className="text-center mb-10">
            <button onClick={() => navigate(returnUrl)}
              className="text-sm text-purple-600 hover:underline mb-5 block mx-auto focus:outline-none focus:ring-2 focus:ring-purple-400 rounded">
              ← Retour à l'événement
            </button>
            <div className="inline-flex items-center gap-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-3">
              <span className="w-2 h-2 rounded-full bg-purple-500 animate-pulse" />
              GSA 2026
            </div>
            <h1 className="text-3xl font-black text-foreground mb-2">Quiz sur l'Autisme</h1>
            <p className="text-muted-foreground text-sm">Répondez à chaque affirmation — vous pouvez passer et revenir.</p>
          </motion.div>

          {/* Barre de progression */}
          <div className="mb-8" role="progressbar" aria-valuenow={answered} aria-valuemin={0} aria-valuemax={questions.length}>
            <div className="flex justify-between text-xs text-muted-foreground mb-2">
              <span>{answered} / {questions.length} réponses</span>
              <span className="font-semibold text-purple-600">{Math.round(progress)}%</span>
            </div>
            <div className="h-2.5 bg-muted rounded-full overflow-hidden">
              <motion.div className="h-full bg-gradient-to-r from-purple-500 to-blue-500 rounded-full"
                animate={{ width: `${progress}%` }} transition={{ duration: 0.4, ease: "easeOut" }} />
            </div>
          </div>

          {/* Dots navigation */}
          <div className="flex items-center justify-center gap-1.5 mb-8 flex-wrap">
            {questions.map((q, i) => (
              <motion.button key={q.id} onClick={() => goTo(i)}
                whileHover={{ scale: 1.3 }} whileTap={{ scale: 0.9 }}
                aria-label={`Question ${i + 1}${answers[q.id] ? " — répondue" : ""}`}
                className={`transition-all rounded-full focus:outline-none focus:ring-2 focus:ring-purple-400 ${
                  i === current ? "w-7 h-3 bg-purple-600" : answers[q.id] ? "w-3 h-3 bg-purple-400" : "w-3 h-3 bg-border hover:bg-purple-200"
                }`} />
            ))}
          </div>

          {/* Card question */}
          <div className="relative" style={{ minHeight: "320px" }}>
            <AnimatePresence mode="wait" custom={direction}>
              {question && (
                <motion.div key={current} custom={direction}
                  initial={{ x: direction * 80, opacity: 0, scale: 0.96 }}
                  animate={{ x: 0, opacity: 1, scale: 1 }}
                  exit={{ x: direction * -80, opacity: 0, scale: 0.96 }}
                  transition={{ duration: 0.3, ease: [0.22, 1, 0.36, 1] }}
                  className="bg-card rounded-3xl border-2 border-border shadow-soft p-8"
                >
                  <div className="flex items-start gap-4 mb-8">
                    <motion.div initial={{ scale: 0 }} animate={{ scale: 1 }} transition={{ delay: 0.1, type: "spring", stiffness: 300 }}
                      className="w-10 h-10 rounded-2xl bg-gradient-to-br from-purple-500 to-blue-500 text-white text-sm font-black flex items-center justify-center flex-shrink-0 shadow-md">
                      {current + 1}
                    </motion.div>
                    <p className="text-foreground font-semibold text-lg leading-relaxed pt-1">{question.text}</p>
                  </div>

                  <div className="space-y-3">
                    {OPTIONS.map((opt, i) => {
                      const isSelected = answers[question.id] === opt.value;
                      return (
                        <motion.button key={opt.value}
                          initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: 0.1 + i * 0.07 }}
                          whileHover={{ scale: 1.02, x: 4 }} whileTap={{ scale: 0.98 }}
                          onClick={() => handleAnswer(opt.value)}
                          aria-pressed={isSelected}
                          className={`w-full flex items-center gap-4 px-5 py-4 rounded-2xl border-2 text-sm font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2 ${
                            isSelected
                              ? `${opt.selectedBg} ${opt.selectedBorder} text-white shadow-md`
                              : `${opt.bg} ${opt.border} ${opt.text} hover:shadow-sm`
                          }`}
                        >
                          <span className="text-xl" aria-hidden="true">{opt.emoji}</span>
                          <span className="flex-1 text-left">{opt.label}</span>
                          {isSelected && (
                            <motion.div initial={{ scale: 0 }} animate={{ scale: 1 }}
                              className="w-5 h-5 rounded-full bg-white/30 flex items-center justify-center flex-shrink-0">
                              <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                              </svg>
                            </motion.div>
                          )}
                        </motion.button>
                      );
                    })}
                  </div>

                  <AnimatePresence>
                    {!answers[question.id] && (
                      <motion.p initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                        className="text-xs text-muted-foreground mt-5 text-center">
                        Vous pouvez passer cette question et y revenir plus tard.
                      </motion.p>
                    )}
                    {justAnswered && answers[question.id] && !isLast && (
                      <motion.p initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }}
                        className="mt-4 text-center text-xs text-purple-600 dark:text-purple-400 font-medium">
                        Passage à la question suivante…
                      </motion.p>
                    )}
                  </AnimatePresence>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          {/* Navigation */}
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}
            className="flex justify-between items-center mt-6">
            <motion.button whileHover={{ scale: 1.03 }} whileTap={{ scale: 0.97 }}
              onClick={() => goTo(current - 1)} disabled={current === 0}
              className="px-5 py-2.5 rounded-xl border-2 border-border text-foreground font-semibold text-sm hover:border-purple-300 disabled:opacity-30 transition-all focus:outline-none focus:ring-2 focus:ring-purple-400">
              ← Précédent
            </motion.button>

            <span className="text-xs text-muted-foreground font-medium">{current + 1} / {questions.length}</span>

            {isLast ? (
              <motion.button whileHover={allAnswered ? { scale: 1.03 } : {}} whileTap={allAnswered ? { scale: 0.97 } : {}}
                onClick={handleSubmit} disabled={!allAnswered || loading}
                className={`px-8 py-2.5 rounded-xl font-bold text-sm transition-all shadow-md focus:outline-none focus:ring-2 focus:ring-purple-400 ${
                  allAnswered && !loading
                    ? "bg-gradient-to-r from-purple-600 to-blue-600 text-white hover:opacity-90"
                    : "bg-muted text-muted-foreground cursor-not-allowed"
                }`}>
                {loading ? (
                  <span className="flex items-center gap-2">
                    <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    Envoi…
                  </span>
                ) : allAnswered ? "Soumettre ✓" : `${questions.length - answered} restante${questions.length - answered > 1 ? "s" : ""}`}
              </motion.button>
            ) : (
              <motion.button whileHover={{ scale: 1.03 }} whileTap={{ scale: 0.97 }}
                onClick={() => goTo(current + 1)}
                className="px-8 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 text-white font-semibold text-sm hover:opacity-90 transition-all shadow-md focus:outline-none focus:ring-2 focus:ring-purple-400">
                Suivant →
              </motion.button>
            )}
          </motion.div>

          <AnimatePresence>
            {error && (
              <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }}
                role="alert"
                className="mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl px-4 py-3 text-sm">
                {error}
              </motion.div>
            )}
          </AnimatePresence>

          <p className="text-center text-xs text-muted-foreground mt-8">
            🔒 Réponses anonymes — Never Limit Children
          </p>
        </div>
      </div>
    </div>
  );
}
