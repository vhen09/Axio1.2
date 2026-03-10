import { useEffect, useRef } from "react";
import katex from "katex";
import "katex/dist/katex.min.css";

interface LaTeXPreviewProps {
  content: string;
  className?: string;
}

const LaTeXPreview = ({ content, className = "" }: LaTeXPreviewProps) => {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!ref.current || !content.trim()) return;
    try {
      katex.render(content, ref.current, {
        throwOnError: false,
        displayMode: true,
        trust: true,
      });
    } catch {
      if (ref.current) ref.current.textContent = content;
    }
  }, [content]);

  if (!content.trim()) {
    return (
      <div className={`text-muted-foreground text-sm italic px-4 py-3 rounded-lg bg-muted/50 ${className}`}>
        LaTeX preview will appear here…
      </div>
    );
  }

  return <div ref={ref} className={`px-4 py-3 rounded-lg bg-muted/50 overflow-x-auto ${className}`} />;
};

export default LaTeXPreview;
