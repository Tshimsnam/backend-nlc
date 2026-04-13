import { useState, useEffect, lazy, Suspense } from "react";
import { useParams, useNavigate } from "react-router-dom";
import Header from "@/components/layout/Header";
import { motion, AnimatePresence } from "framer-motion";

const CertificatParticipation = lazy(() => import("./CertificatParticipation"));

const API_BASE = import.meta.env.VITE_API_URL ?? "http://localhost:8000/api";

type TsaQuestion = { id: number; text: string; options: Record<string, string> | string[] };
type FormData = {
  full_name: string; etablissement: string; profil: string; profil_autre: string;
  contact: string; date_colloque: string; duree_session: string;
  adequation_theme: string; aspects_pertinents: string; sujets_manquants: string;
  clarte_presentations: string; maintien_attention: string;
  organisation_generale: string; respect_horaires: string; logistique_commentaire: string;
  opportunites_interaction: string; contacts_professionnels: string;
  enseignements_tires: string; application_enseignements: string;
  note_globale: number | "";
  points_forts: string; suggestions_amelioration: string; commentaires_additionnels: string;
  tsa_answers: string[];
};

const INIT: FormData = {
  full_name: "", etablissement: "", profil: "", profil_autre: "", contact: "",
  date_colloque: "", duree_session: "",
  adequation_theme: "", aspects_pertinents: "", sujets_manquants: "",
  clarte_presentations: "", maintien_attention: "",
  organisation_generale: "", respect_horaires: "", logistique_commentaire: "",
  opportunites_interaction: "", contacts_professionnels: "",
  enseignements_tires: "", application_enseignements: "",
  note_globale: "", points_forts: "", suggestions_amelioration: "",
  commentaires_additionnels: "", tsa_answers: [],
};

const SECTIONS = [
  "Informations personnelles", "Contenu du colloque", "Qualité des présentations",
  "Organisation & logistique", "Interaction & Networking", "Retour sur l'apprentissage",
  "Évaluation globale", "Commentaires", "Quiz TSA",
];

// ── UI helpers ────────────────────────────────────────────────────────────────
const Label = ({ children }: { children: React.ReactNode }) => (
  <p className="text-sm font-semibold text-foreground mb-2">{children}</p>
);

const TextArea = ({ value, onChange, placeholder }: {
  value: string; onChange: (v: string) => void; placeholder?: string;
}) => (
  <textarea
    value={value} onChange={e => onChange(e.target.value)}
    placeholder={placeholder ?? "Votre réponse…"} rows={3}
    className="w-full border border-border rounded-xl px-4 py-3 text-sm text-foreground bg-background focus:outline-none focus:ring-2 focus:ring-purple-400 resize-none"
  />
);

const RadioGroup = ({ options, value, onChange }: {
  options: { label: string; value: string }[];
  value: string; onChange: (v: string) => void;
}) => (
  <div className="space-y-2">
    {options.map(o => (
      <label key={o.value} className={`flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all ${
        value === o.value
          ? "border-purple-400 bg-purple-50 dark:bg-purple-900/20"
          : "border-border hover:border-purple-300 dark:hover:border-purple-600"
      }`}>
        <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 ${
          value === o.value ? "border-purple-500" : "border-muted-foreground"
        }`}>
          {value === o.value && <div className="w-2.5 h-2.5 rounded-full bg-purple-500" />}
        </div>
        <input type="radio" className="hidden" value={o.value} checked={value === o.value} onChange={() => onChange(o.value)} />
        <span className="text-sm text-foreground">{o.label}</span>
      </label>
    ))}
  </div>
);

const SectionTitle = ({ num, title, subtitle }: { num: number; title: string; subtitle?: string }) => (
  <div className="mb-6">
    <div className="flex items-center gap-3 mb-1">
      <div className="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 text-white text-sm font-black flex items-center justify-center flex-shrink-0">
        {num}
      </div>
      <h2 className="text-lg font-black text-foreground">{title}</h2>
    </div>
    {subtitle && <p className="text-xs text-muted-foreground pl-11">{subtitle}</p>}
  </div>
);

// ── Page ──────────────────────────────────────────────────────────────────────
export default function ColloqueEvaluationPage() {
  const { slug } = useParams();
  const navigate = useNavigate();

  const [form, setForm]           = useState<FormData>(INIT);
  const [section, setSection]     = useState(0);
  const [direction, setDirection] = useState(1);
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading]     = useState(false);
  const [error, setError]         = useState<string | null>(null);
  const [tsaQuestions, setTsaQuestions] = useState<TsaQuestion[]>([]);
  const [tsaLoading, setTsaLoading]     = useState(false);

  const goTo = (i: number) => {
    setDirection(i > section ? 1 : -1);
    setSection(i);
  };

  useEffect(() => {
    setTsaLoading(true);
    // Utilise la route par slug si disponible
    const url = slug
      ? `${API_BASE}/evenements/${slug}/evaluation/questions`
      : `${API_BASE}/colloque/questions`;
    fetch(url)
      .then(r => r.json())
      .then(d => {
        const questions = d.questions ?? [];
        // Les options peuvent être un objet JSON stringifié ou déjà parsé
        const parsed = questions.map((q: TsaQuestion) => ({
          ...q,
          options: typeof q.options === "string" ? JSON.parse(q.options) : q.options,
        }));
        setTsaQuestions(parsed);
        setForm(prev => ({ ...prev, tsa_answers: new Array(parsed.length).fill("") }));
      })
      .catch(() => {})
      .finally(() => setTsaLoading(false));
  }, [slug]);

  const set = (key: keyof FormData) => (val: string | number) =>
    setForm(prev => ({ ...prev, [key]: val }));

  const setTsaAnswer = (index: number, val: string) =>
    setForm(prev => {
      const answers = [...prev.tsa_answers];
      answers[index] = val;
      return { ...prev, tsa_answers: answers };
    });

  const handleSubmit = async () => {
    setLoading(true); setError(null);
    try {
      const res = await fetch(
        slug ? `${API_BASE}/evenements/${slug}/evaluation/submit` : `${API_BASE}/colloque/evaluate`,
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(form),
        }
      );
      const data = await res.json();
      if (!res.ok) { setError(data.message ?? "Erreur lors de l'envoi."); return; }
      setSubmitted(true);
    } catch {
      setError("Impossible de contacter le serveur.");
    } finally {
      setLoading(false);
    }
  };

  const returnUrl = slug ? `/evenements/${slug}?done=true#test` : "/evenements";

  if (submitted) {
    return (
      <div className="min-h-screen bg-background">
        <Header />
        <Suspense fallback={<div className="min-h-screen flex items-center justify-center text-muted-foreground">Chargement…</div>}>
          <div className="pt-28">
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
              className="text-center pt-4 pb-6 px-4"
            >
              <motion.div
                initial={{ scale: 0 }}
                animate={{ scale: 1 }}
                transition={{ delay: 0.2, type: "spring", stiffness: 300 }}
                className="text-5xl mb-3"
              >🎉</motion.div>
              <h2 className="text-2xl font-bold text-foreground mb-1">Merci pour votre évaluation !</h2>
              <p className="text-muted-foreground text-sm mb-4">Vos réponses ont été enregistrées. Téléchargez votre certificat ci-dessous.</p>
              <button onClick={() => navigate(returnUrl)} className="text-sm text-purple-600 hover:underline">
                ← Retour à l'événement
              </button>
            </motion.div>
            <CertificatParticipation
              fullName={form.full_name || "Participant(e)"}
              etablissement={form.etablissement}
              dateColloque={form.date_colloque}
              dureeSession={form.duree_session}
              eventTitle="Grand Salon de l'Autisme (GSA) 2026 — Le TSA et la scolarité"
            />
          </div>
        </Suspense>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-secondary">
      <Header />
      <div className="pt-28 pb-12 px-4">
        <div className="max-w-2xl mx-auto">

          {/* Header */}
          <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} className="text-center mb-8">
            <button onClick={() => navigate(returnUrl)} className="text-sm text-purple-600 hover:underline mb-4 block mx-auto focus:outline-none focus:ring-2 focus:ring-purple-400 rounded">
              ← Retour à l'événement
            </button>
            <div className="inline-flex items-center gap-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-3">
              <span className="w-2 h-2 rounded-full bg-purple-500 animate-pulse" />
              GSA 2026
            </div>
            <h1 className="text-3xl font-black text-foreground mb-2">Évaluation du Colloque</h1>
            <p className="text-muted-foreground text-sm max-w-lg mx-auto leading-relaxed">
              Votre évaluation nous aidera à comprendre vos impressions et à améliorer nos futurs événements.
              Vos réponses seront traitées de manière confidentielle.
            </p>
          </motion.div>

          {/* Stepper */}
          <motion.div
            initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }}
            className="flex gap-1 mb-8 overflow-x-auto pb-2"
          >
            {SECTIONS.map((s, i) => (
              <motion.button
                key={i}
                onClick={() => goTo(i)}
                whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}
                className={`flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold transition-all focus:outline-none focus:ring-2 focus:ring-purple-400 ${
                  i === section
                    ? "bg-purple-600 text-white shadow-md"
                    : "bg-card text-muted-foreground border border-border hover:border-purple-300"
                }`}>
                {i + 1}. {s.split(" ")[0]}
              </motion.button>
            ))}
          </motion.div>

          {/* Barre de progression */}
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.15 }} className="mb-6">
            <div className="h-1.5 bg-muted rounded-full overflow-hidden">
              <motion.div
                className="h-full bg-gradient-to-r from-purple-500 to-blue-500 rounded-full"
                animate={{ width: `${((section + 1) / SECTIONS.length) * 100}%` }}
                transition={{ duration: 0.4, ease: "easeOut" }}
              />
            </div>
            <p className="text-xs text-muted-foreground mt-1 text-right">{section + 1} / {SECTIONS.length}</p>
          </motion.div>

          {/* Card animée */}
          <div className="relative overflow-hidden" style={{ minHeight: "300px" }}>
            <AnimatePresence mode="wait" custom={direction}>
              <motion.div
                key={section}
                custom={direction}
                initial={{ x: direction * 60, opacity: 0, scale: 0.97 }}
                animate={{ x: 0, opacity: 1, scale: 1 }}
                exit={{ x: direction * -60, opacity: 0, scale: 0.97 }}
                transition={{ duration: 0.3, ease: [0.22, 1, 0.36, 1] }}
                className="bg-card rounded-3xl shadow-sm border border-border p-6 mb-6"
              >

            {section === 0 && (
              <>
                <SectionTitle num={1} title="Informations personnelles" />
                <div className="space-y-4">
                  <div>
                    <Label>Nom complet</Label>
                    <input value={form.full_name} onChange={e => set("full_name")(e.target.value)} placeholder="Votre nom et prénom"
                      className="w-full border border-border rounded-xl px-4 py-3 text-sm text-foreground bg-background focus:outline-none focus:ring-2 focus:ring-purple-400" />
                  </div>
                  <div>
                    <Label>Établissement</Label>
                    <input value={form.etablissement} onChange={e => set("etablissement")(e.target.value)} placeholder="Nom de votre établissement"
                      className="w-full border border-border rounded-xl px-4 py-3 text-sm text-foreground bg-background focus:outline-none focus:ring-2 focus:ring-purple-400" />
                  </div>
                  <div>
                    <Label>Je suis</Label>
                    <div className="grid grid-cols-2 gap-2">
                      {["Étudiant(e)", "Docteur(e)", "Professeur(e)", "Parent", "Enseignant(e)", "Autiste", "Autre"].map(p => (
                        <label key={p} className={`flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer text-sm transition-all ${
                          form.profil === p
                            ? "border-purple-400 bg-purple-50 dark:bg-purple-900/20 font-semibold text-purple-700 dark:text-purple-300"
                            : "border-border text-foreground hover:border-purple-300"
                        }`}>
                          <input type="radio" className="hidden" checked={form.profil === p} onChange={() => set("profil")(p)} />
                          <div className={`w-4 h-4 rounded-full border-2 flex-shrink-0 ${form.profil === p ? "border-purple-500 bg-purple-500" : "border-muted-foreground"}`} />
                          {p}
                        </label>
                      ))}
                    </div>
                    {form.profil === "Autre" && (
                      <input value={form.profil_autre} onChange={e => set("profil_autre")(e.target.value)} placeholder="Précisez…"
                        className="mt-2 w-full border border-border rounded-xl px-4 py-3 text-sm text-foreground bg-background focus:outline-none focus:ring-2 focus:ring-purple-400" />
                    )}
                  </div>
                  <div>
                    <Label>Contact</Label>
                    <input value={form.contact} onChange={e => set("contact")(e.target.value)} placeholder="Téléphone ou email"
                      className="w-full border border-border rounded-xl px-4 py-3 text-sm text-foreground bg-background focus:outline-none focus:ring-2 focus:ring-purple-400" />
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <Label>Date du colloque</Label>
                      <div className="flex gap-2">
                        {["2026-04-15", "2026-04-16"].map(d => (
                          <label key={d} className={`flex-1 flex items-center justify-center gap-2 p-3 rounded-xl border-2 cursor-pointer text-sm font-semibold transition-all ${
                            form.date_colloque === d
                              ? "border-purple-400 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300"
                              : "border-border text-foreground hover:border-purple-300"
                          }`}>
                            <input type="radio" className="hidden" checked={form.date_colloque === d} onChange={() => set("date_colloque")(d)} />
                            <div className={`w-4 h-4 rounded-full border-2 flex-shrink-0 ${form.date_colloque === d ? "border-purple-500 bg-purple-500" : "border-muted-foreground"}`} />
                            {d === "2026-04-15" ? "15 Avril" : "16 Avril"}
                          </label>
                        ))}
                      </div>
                    </div>
                    <div>
                      <Label>Durée de la session</Label>
                      <input value={form.duree_session} onChange={e => set("duree_session")(e.target.value)} placeholder="Ex: 3h"
                        className="w-full border border-border rounded-xl px-4 py-3 text-sm text-foreground bg-background focus:outline-none focus:ring-2 focus:ring-purple-400" />
                    </div>
                  </div>
                </div>
              </>
            )}

            {section === 1 && (
              <>
                <SectionTitle num={2} title="Contenu du colloque" />
                <div className="space-y-6">
                  <div>
                    <Label>Le thème du colloque était-il en adéquation avec vos attentes ?</Label>
                    <RadioGroup value={form.adequation_theme} onChange={set("adequation_theme")} options={[
                      { label: "Très en adéquation", value: "tres_adequat" },
                      { label: "En adéquation", value: "adequat" },
                      { label: "Neutre", value: "neutre" },
                      { label: "Pas vraiment en adéquation", value: "pas_vraiment" },
                      { label: "Pas du tout en adéquation", value: "pas_du_tout" },
                    ]} />
                  </div>
                  <div><Label>Quels aspects du contenu avez-vous trouvés les plus pertinents ?</Label><TextArea value={form.aspects_pertinents} onChange={set("aspects_pertinents")} /></div>
                  <div><Label>Y a-t-il des sujets que vous auriez souhaité voir abordés ?</Label><TextArea value={form.sujets_manquants} onChange={set("sujets_manquants")} /></div>
                </div>
              </>
            )}

            {section === 2 && (
              <>
                <SectionTitle num={3} title="Qualité des présentations" />
                <div className="space-y-6">
                  <div>
                    <Label>Comment évaluez-vous la clarté et la pertinence des présentations ?</Label>
                    <RadioGroup value={form.clarte_presentations} onChange={set("clarte_presentations")} options={[
                      { label: "Excellente", value: "excellente" }, { label: "Très bonne", value: "tres_bonne" },
                      { label: "Bonne", value: "bonne" }, { label: "Acceptable", value: "acceptable" },
                      { label: "Insatisfaisante", value: "insatisfaisante" },
                    ]} />
                  </div>
                  <div>
                    <Label>Les intervenants ont-ils réussi à maintenir votre attention ?</Label>
                    <RadioGroup value={form.maintien_attention} onChange={set("maintien_attention")} options={[
                      { label: "Toujours", value: "toujours" }, { label: "Souvent", value: "souvent" },
                      { label: "Parfois", value: "parfois" }, { label: "Jamais", value: "jamais" },
                    ]} />
                  </div>
                </div>
              </>
            )}

            {section === 3 && (
              <>
                <SectionTitle num={4} title="Organisation & logistique" />
                <div className="space-y-6">
                  <div>
                    <Label>Comment évaluez-vous l'organisation générale ?</Label>
                    <RadioGroup value={form.organisation_generale} onChange={set("organisation_generale")} options={[
                      { label: "Excellente", value: "excellente" }, { label: "Très bonne", value: "tres_bonne" },
                      { label: "Bonne", value: "bonne" }, { label: "Acceptable", value: "acceptable" },
                      { label: "À améliorer", value: "a_ameliorer" },
                    ]} />
                  </div>
                  <div>
                    <Label>Les horaires ont-ils été respectés ?</Label>
                    <RadioGroup value={form.respect_horaires} onChange={set("respect_horaires")} options={[
                      { label: "Toujours", value: "toujours" }, { label: "La plupart du temps", value: "la_plupart" },
                      { label: "Parfois", value: "parfois" }, { label: "Rarement", value: "rarement" },
                      { label: "Jamais", value: "jamais" },
                    ]} />
                  </div>
                  <div><Label>Commentaires sur la logistique</Label><TextArea value={form.logistique_commentaire} onChange={set("logistique_commentaire")} /></div>
                </div>
              </>
            )}

            {section === 4 && (
              <>
                <SectionTitle num={5} title="Interaction & Networking" />
                <div className="space-y-6">
                  <div>
                    <Label>Avez-vous eu l'opportunité d'interagir avec les autres participants ?</Label>
                    <RadioGroup value={form.opportunites_interaction} onChange={set("opportunites_interaction")} options={[
                      { label: "Beaucoup d'opportunités", value: "beaucoup" },
                      { label: "Quelques opportunités", value: "quelques" },
                      { label: "Peu d'opportunités", value: "peu" },
                      { label: "Aucune opportunité", value: "aucune" },
                    ]} />
                  </div>
                  <div><Label>Avez-vous établi des contacts professionnels utiles ?</Label><TextArea value={form.contacts_professionnels} onChange={set("contacts_professionnels")} placeholder="Décrivez vos échanges…" /></div>
                </div>
              </>
            )}

            {section === 5 && (
              <>
                <SectionTitle num={6} title="Retour sur l'apprentissage" />
                <div className="space-y-6">
                  <div><Label>Quels sont les principaux enseignements que vous avez tirés ?</Label><TextArea value={form.enseignements_tires} onChange={set("enseignements_tires")} /></div>
                  <div><Label>Comment prévoyez-vous appliquer ces enseignements ?</Label><TextArea value={form.application_enseignements} onChange={set("application_enseignements")} /></div>
                </div>
              </>
            )}

            {section === 6 && (
              <>
                <SectionTitle num={7} title="Évaluation globale" />
                <div className="space-y-6">
                  <div>
                    <Label>Note globale (1 à 10)</Label>
                    <div className="flex gap-2 flex-wrap">
                      {[1,2,3,4,5,6,7,8,9,10].map(n => (
                        <button key={n} onClick={() => set("note_globale")(n)}
                          className={`w-10 h-10 rounded-xl font-bold text-sm transition-all ${
                            form.note_globale === n
                              ? "bg-gradient-to-br from-purple-500 to-blue-500 text-white shadow-md"
                              : "bg-muted text-foreground hover:bg-purple-100 dark:hover:bg-purple-900/30"
                          }`}>
                          {n}
                        </button>
                      ))}
                    </div>
                  </div>
                  <div><Label>Quels sont les points forts du colloque ?</Label><TextArea value={form.points_forts} onChange={set("points_forts")} /></div>
                  <div><Label>Comment pourrions-nous améliorer ce colloque ?</Label><TextArea value={form.suggestions_amelioration} onChange={set("suggestions_amelioration")} /></div>
                </div>
              </>
            )}

            {section === 7 && (
              <>
                <SectionTitle num={8} title="Commentaires additionnels" />
                <div>
                  <Label>Autres commentaires, suggestions ou remarques</Label>
                  <TextArea value={form.commentaires_additionnels} onChange={set("commentaires_additionnels")} placeholder="Tout ce que vous souhaitez partager…" />
                </div>
              </>
            )}

            {section === 8 && (
              <>
                <SectionTitle num={9} title="Quiz TSA" subtitle="Questionnaire d'évaluation des connaissances sur les Troubles du Spectre de l'Autisme" />
                {tsaLoading ? (
                  <div className="text-center py-8 text-muted-foreground text-sm">Chargement des questions…</div>
                ) : tsaQuestions.length === 0 ? (
                  <div className="text-center py-8 text-muted-foreground text-sm italic">Aucune question disponible pour le moment.</div>
                ) : (
                  <div className="space-y-6">
                    {tsaQuestions.map((q, i) => (
                      <div key={q.id}>
                        <Label>{i + 1}. {q.text}</Label>
                        <RadioGroup
                          value={form.tsa_answers[i] ?? ""}
                          onChange={val => setTsaAnswer(i, val)}
                          options={
                            Array.isArray(q.options)
                              ? q.options.map((opt: string, j: number) => ({
                                  label: opt,
                                  value: String.fromCharCode(65 + j),
                                }))
                              : Object.entries(q.options as Record<string, string>).map(([k, v]) => ({
                                  label: v,
                                  value: k,
                                }))
                          }
                        />
                      </div>
                    ))}
                  </div>
                )}
              </>
            )}
          </motion.div>
            </AnimatePresence>
          </div>

          <AnimatePresence>
          {error && (
            <motion.div
              initial={{ opacity: 0, y: -8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }}
              role="alert"
              className="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl px-4 py-3 text-sm">
              {error}
            </motion.div>
          )}
          </AnimatePresence>

          {/* Navigation */}
          <motion.div
            initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}
            className="flex justify-between items-center mt-2"
          >
            <motion.button
              whileHover={{ scale: 1.03 }} whileTap={{ scale: 0.97 }}
              onClick={() => goTo(section - 1)} disabled={section === 0}
              className="px-6 py-3 rounded-xl border-2 border-border text-foreground font-semibold text-sm hover:border-purple-300 disabled:opacity-30 transition-all focus:outline-none focus:ring-2 focus:ring-purple-400">
              ← Précédent
            </motion.button>
            {section < SECTIONS.length - 1 ? (
              <motion.button
                whileHover={{ scale: 1.03 }} whileTap={{ scale: 0.97 }}
                onClick={() => goTo(section + 1)}
                className="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 text-white font-semibold text-sm hover:opacity-90 transition-all shadow-md focus:outline-none focus:ring-2 focus:ring-purple-400">
                Suivant →
              </motion.button>
            ) : (
              <motion.button
                whileHover={!loading ? { scale: 1.03 } : {}} whileTap={!loading ? { scale: 0.97 } : {}}
                onClick={handleSubmit} disabled={loading}
                className="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold text-sm hover:opacity-90 disabled:opacity-60 transition-all shadow-md focus:outline-none focus:ring-2 focus:ring-purple-400">
                {loading ? (
                  <span className="flex items-center gap-2">
                    <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    Envoi…
                  </span>
                ) : "Soumettre ✓"}
              </motion.button>
            )}
          </motion.div>

          <p className="text-center text-xs text-muted-foreground mt-4">
            🔒 Vos réponses sont traitées de manière confidentielle — Never Limit Children
          </p>
        </div>
      </div>
    </div>
  );
}
