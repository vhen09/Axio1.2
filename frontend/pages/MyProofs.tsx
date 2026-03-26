import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAppState } from "./AppContext";
import { Button } from "./button";
import { Input } from "./input";
import { Trash2, Search, ArrowRight } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import LaTeXPreview from "./LaTeXPreview";

const MyProofs = () => {
  const { savedProofs, deleteProof, loadProof } = useAppState();
  const [search, setSearch] = useState("");
  const navigate = useNavigate();

  const filtered = savedProofs.filter(
    (proof) => proof.theorem.toLowerCase().includes(search.toLowerCase())
  );

  const handleOpen = (proof: typeof savedProofs[0]) => {
    loadProof(proof);
    navigate("/");
  };

  return (
    <div className="max-w-3xl mx-auto space-y-6 animate-fade-in">
      <h1 className="text-2xl font-bold gradient-text">My Proofs</h1>

      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          value={search}
          onChange={(event) => setSearch(event.target.value)}
          placeholder="Search proofs…"
          className="pl-10"
        />
      </div>

      {filtered.length === 0 ? (
        <div className="text-center py-16 text-muted-foreground">
          <p>No proofs found.</p>
        </div>
      ) : (
        <div className="space-y-3">
          <AnimatePresence>
            {filtered.map((proof) => (
              <motion.div
                key={proof.id}
                layout
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, x: -50 }}
                className="glass-card p-4 hover-lift"
              >
                <div className="flex items-start justify-between gap-4">
                  <div className="flex-1 min-w-0 space-y-2">
                    <LaTeXPreview content={proof.theorem} className="!bg-transparent !p-0" />
                    <div className="flex items-center gap-3 text-xs text-muted-foreground">
                      <span>{proof.date}</span>
                      <span>{proof.steps.length} steps</span>
                      <span className="text-success font-medium">{proof.accuracy}% accuracy</span>
                    </div>
                  </div>
                  <div className="flex items-center gap-1 flex-shrink-0">
                    <Button variant="ghost" size="sm" onClick={() => handleOpen(proof)}>
                      <ArrowRight className="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="sm" className="text-destructive" onClick={() => deleteProof(proof.id)}>
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
      )}
    </div>
  );
};

export default MyProofs;
