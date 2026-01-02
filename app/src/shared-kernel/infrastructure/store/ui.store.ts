import { create } from "zustand";
import { persist } from "zustand/middleware";

type ColorScheme = "light" | "dark" | "auto";

interface UiState {
  colorScheme: ColorScheme;
  sidebarOpen: boolean;
  setColorScheme: (scheme: ColorScheme) => void;
  toggleSidebar: () => void;
  setSidebarOpen: (open: boolean) => void;
}

export const useUiStore = create<UiState>()(
  persist(
    (set) => ({
      colorScheme: "auto",
      sidebarOpen: true,
      setColorScheme: (scheme) => set({ colorScheme: scheme }),
      toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
      setSidebarOpen: (open) => set({ sidebarOpen: open }),
    }),
    {
      name: "dairectiv-ui",
      partialize: (state) => ({
        colorScheme: state.colorScheme,
        sidebarOpen: state.sidebarOpen,
      }),
    },
  ),
);
