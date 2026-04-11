import { useRef, useState } from "react";

// ─── Types ────────────────────────────────────────────────────────────────────
interface CertificatProps {
  fullName: string;
  etablissement?: string;
  dateColloque?: string;
  dureeSession?: string;
  eventTitle?: string;
}

// ─── Composant certificat (rendu HTML → PDF) ──────────────────────────────────
function CertificatCard({ fullName, etablissement, dateColloque, dureeSession, eventTitle }: CertificatProps) {
  const date = dateColloque
    ? new Date(dateColloque).toLocaleDateString("fr-FR", { day: "numeric", month: "long", year: "numeric" })
    : "2026";
  const year = dateColloque ? new Date(dateColloque).getFullYear() : 2026;
  const id   = Math.floor(10000000000 + Math.random() * 90000000000);

  return (
    <div
      style={{
        width: "842px", height: "595px",
        background: "#ffffff",
        position: "relative",
        fontFamily: "Georgia, 'Times New Roman', serif",
        overflow: "hidden",
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
      }}
    >
      {/* Bordure extérieure navy */}
      <div style={{
        position: "absolute", inset: 0,
        border: "28px solid #1a2a5e",
        pointerEvents: "none",
      }} />
      {/* Bordure intérieure or */}
      <div style={{
        position: "absolute", inset: "28px",
        border: "3px solid #c9a84c",
        pointerEvents: "none",
      }} />
      {/* Filet intérieur fin */}
      <div style={{
        position: "absolute", inset: "36px",
        border: "1px solid #c9a84c",
        pointerEvents: "none",
      }} />

      {/* Coins décoratifs */}
      {[
        { top: "28px", left: "28px" },
        { top: "28px", right: "28px" },
        { bottom: "28px", left: "28px" },
        { bottom: "28px", right: "28px" },
      ].map((pos, i) => (
        <div key={i} style={{
          position: "absolute", ...pos,
          width: "32px", height: "32px",
          background: "#c9a84c",
          borderRadius: "50%",
          border: "3px solid #1a2a5e",
        }} />
      ))}

      {/* Contenu centré */}
      <div style={{ textAlign: "center", padding: "0 80px", position: "relative", zIndex: 1, width: "100%" }}>

        {/* Logo NLC */}
        <div style={{ marginBottom: "12px" }}>
          <img
            src="/logo-nlc-blanc.png"
            alt="NLC"
            style={{ height: "36px", filter: "invert(1) sepia(1) saturate(2) hue-rotate(190deg)", opacity: 0.85 }}
            onError={e => (e.currentTarget.style.display = "none")}
          />
        </div>

        {/* Titre */}
        <h1 style={{
          fontSize: "32px", fontWeight: "400", color: "#1a2a5e",
          letterSpacing: "2px", margin: "0 0 6px",
        }}>
          Certificat de participation
        </h1>

        {/* Sous-titre */}
        <p style={{
          fontSize: "10px", fontWeight: "700", color: "#c9a84c",
          letterSpacing: "3px", textTransform: "uppercase", margin: "0 0 16px",
        }}>
          Nous certifions par la présente que
        </p>

        {/* Séparateur or */}
        <div style={{ width: "120px", height: "2px", background: "#c9a84c", margin: "0 auto 14px" }} />

        {/* Nom en script */}
        <p style={{
          fontSize: "44px", color: "#1a2a5e", margin: "0 0 14px",
          fontFamily: "'Dancing Script', 'Brush Script MT', cursive",
          fontWeight: "700",
        }}>
          {fullName || "Participant(e)"}
        </p>

        {/* Séparateur */}
        <div style={{ width: "200px", height: "1px", background: "#c9a84c", margin: "0 auto 14px" }} />

        {/* Texte de certification */}
        <p style={{
          fontSize: "12px", color: "#333", lineHeight: "1.7",
          maxWidth: "520px", margin: "0 auto 20px",
        }}>
          a participé au colloque{" "}
          <strong style={{ color: "#1a2a5e" }}>
            {eventTitle ?? "Grand Salon de l'Autisme (GSA) 2026"}
          </strong>
          {etablissement ? `, représentant ${etablissement},` : ""}
          {" "}organisé par <strong style={{ color: "#1a2a5e" }}>Never Limit Children (NLC)</strong>
          {dateColloque ? ` le ${date}` : ""}
          {dureeSession ? `, d'une durée de ${dureeSession}` : ""}.
        </p>

        {/* Zone signatures + sceau */}
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end", padding: "0 40px" }}>

          {/* Signature gauche */}
          <div style={{ textAlign: "center", minWidth: "140px" }}>
            <div style={{
              fontFamily: "'Dancing Script', cursive", fontSize: "22px",
              color: "#1a2a5e", borderBottom: "1px solid #1a2a5e",
              paddingBottom: "4px", marginBottom: "4px",
            }}>
              NLC Direction
            </div>
            <p style={{ fontSize: "9px", fontWeight: "700", color: "#1a2a5e", letterSpacing: "1px", margin: 0 }}>
              NEVER LIMIT CHILDREN
            </p>
            <p style={{ fontSize: "9px", color: "#666", margin: "2px 0 0" }}>Direction</p>
          </div>

          {/* Sceau central */}
          <div style={{
            width: "80px", height: "80px", borderRadius: "50%",
            border: "3px solid #c9a84c",
            display: "flex", flexDirection: "column",
            alignItems: "center", justifyContent: "center",
            background: "rgba(201,168,76,0.08)",
          }}>
            <div style={{ fontSize: "10px", color: "#c9a84c", letterSpacing: "1px" }}>★ ★ ★</div>
            <div style={{ fontSize: "18px", fontWeight: "700", color: "#1a2a5e" }}>{year}</div>
            <div style={{ fontSize: "7px", color: "#c9a84c", letterSpacing: "1px", textTransform: "uppercase" }}>GSA</div>
          </div>

          {/* Signature droite */}
          <div style={{ textAlign: "center", minWidth: "140px" }}>
            <div style={{
              fontFamily: "'Dancing Script', cursive", fontSize: "22px",
              color: "#1a2a5e", borderBottom: "1px solid #1a2a5e",
              paddingBottom: "4px", marginBottom: "4px",
            }}>
              Comité GSA
            </div>
            <p style={{ fontSize: "9px", fontWeight: "700", color: "#1a2a5e", letterSpacing: "1px", margin: 0 }}>
              COMITÉ ORGANISATEUR
            </p>
            <p style={{ fontSize: "9px", color: "#666", margin: "2px 0 0" }}>GSA 2026</p>
          </div>
        </div>

        {/* Pied de certificat */}
        <div style={{
          display: "flex", justifyContent: "space-between",
          marginTop: "14px", padding: "0 40px",
          fontSize: "9px", color: "#888",
        }}>
          <span>Identifiant : {id}</span>
          <span style={{ color: "#c9a84c", fontSize: "8px" }}>
            Développé par Franck Kapuya – www.franckkapuya.com
          </span>
          <span>Date de délivrance : {new Date().toLocaleDateString("fr-FR")}</span>
        </div>
      </div>
    </div>
  );
}

// ─── Page / composant exportable ─────────────────────────────────────────────
export default function CertificatParticipation(props: CertificatProps) {
  const certRef = useRef<HTMLDivElement>(null);
  const [loading, setLoading] = useState(false);

  const downloadPDF = async () => {
    if (!certRef.current) return;
    setLoading(true);

    // Charger les libs dynamiquement
    const [{ default: html2canvas }, { jsPDF }] = await Promise.all([
      import("html2canvas"),
      import("jspdf").then(m => ({ jsPDF: m.jsPDF })),
    ]);

    const canvas = await html2canvas(certRef.current, {
      scale: 2,
      useCORS: true,
      backgroundColor: "#ffffff",
      width: 842,
      height: 595,
      windowWidth: 842,
    });

    const imgData = canvas.toDataURL("image/jpeg", 0.97);
    const pdf = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });
    pdf.addImage(imgData, "JPEG", 0, 0, 297, 210);

    const safeName = (props.fullName ?? "certificat").replace(/\s+/g, "_").toLowerCase();
    pdf.save(`certificat_gsa2026_${safeName}.pdf`);
    setLoading(false);
  };

  return (
    <div className="min-h-screen bg-slate-100 flex flex-col items-center justify-center py-10 px-4">

      {/* Aperçu du certificat */}
      <div
        style={{ transform: "scale(0.85)", transformOrigin: "top center", marginBottom: "-60px" }}
        className="shadow-2xl rounded-sm"
      >
        <div ref={certRef}>
          <CertificatCard {...props} />
        </div>
      </div>

      {/* Bouton téléchargement */}
      <button
        onClick={downloadPDF}
        disabled={loading}
        className="mt-8 flex items-center gap-3 bg-gradient-to-r from-[#1a2a5e] to-[#2a3f8f] hover:opacity-90 text-white px-8 py-4 rounded-2xl font-bold text-base shadow-xl transition-all disabled:opacity-60"
      >
        {loading ? (
          <>
            <svg className="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="white" strokeWidth="4"/>
              <path className="opacity-75" fill="white" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Génération en cours…
          </>
        ) : (
          <>
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Télécharger mon certificat (PDF)
          </>
        )}
      </button>

      <p className="text-xs text-slate-400 mt-3">Format A4 paysage — haute résolution</p>
    </div>
  );
}
