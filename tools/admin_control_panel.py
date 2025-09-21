import subprocess
import sys
import threading
import tkinter as tk
from tkinter import scrolledtext, messagebox
from tkinter import ttk
import os

# Configure paths
REPO_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
PHP_ARTISAN = os.path.join(REPO_ROOT, 'artisan')
PYTHON_EXE = sys.executable or 'python'

COMMANDS = {
    "Sync Resolved to MySQL": ["php", "artisan", "sync:incident-logs"],
    "Predict Severity (Mobile)": ["php", "artisan", "incidents:predict-severity"],
    # Long-running dev servers/tools
    "Start PHP Server (serve)": ["php", "artisan", "serve"],
    "Start Severity API (python)": [PYTHON_EXE, "predict_severity_api.py"],
}

# Labels considered long-running dev tasks; we disable only their own button
LONG_RUNNING_LABELS = {
    "Start PHP Server (serve)",
    "Start Severity API (python)",
}

class AdminControlPanel(tk.Tk):
    def __init__(self):
        super().__init__()
        self.title("CAP102 Admin Control Panel")
        self.geometry("750x550")
        self.minsize(600,450)
        self.running = False
        self.current_process = None
        self.current_label = None
        self.command_buttons = []
        self.buttons = {}
        self.processes = {}  # label -> Popen
        
        # Set window icon and background
        self.configure(bg='#f0f0f0')
        
        # Apply enhanced ttk theme and styles
        try:
            style = ttk.Style(self)
            # Prefer Windows native theme
            available_themes = style.theme_names()
            preferred_themes = ["winnative", "vista", "xpnative", "clam", "alt", "default"]
            for theme in preferred_themes:
                if theme in available_themes:
                    style.theme_use(theme)
                    break
            
            # Enhanced styling
            style.configure("Header.TLabel", 
                          font=("Segoe UI", 20, "bold"), 
                          foreground="#2c3e50",
                          background="#f0f0f0")
            style.configure("Subheader.TLabel", 
                          font=("Segoe UI", 11), 
                          foreground="#7f8c8d",
                          background="#f0f0f0")
            
            # Enhanced button styles
            style.configure("Action.TButton", 
                          font=("Segoe UI", 10, "bold"),
                          padding=(15, 8),
                          relief="flat")
            style.configure("LongRunning.TButton", 
                          font=("Segoe UI", 10, "bold"),
                          padding=(15, 8),
                          relief="flat")
            style.configure("Control.TButton", 
                          font=("Segoe UI", 9),
                          padding=(12, 6),
                          relief="flat")
            style.configure("Status.TLabel", 
                          font=("Segoe UI", 9),
                          foreground="#34495e",
                          background="#ecf0f1")
            
            # Map button colors
            style.map("Action.TButton",
                     background=[("active", "#3498db"), ("pressed", "#2980b9")],
                     foreground=[("active", "white"), ("pressed", "white")])
            style.map("LongRunning.TButton",
                     background=[("active", "#e74c3c"), ("pressed", "#c0392b")],
                     foreground=[("active", "white"), ("pressed", "white")])
            style.map("Control.TButton",
                     background=[("active", "#95a5a6"), ("pressed", "#7f8c8d")],
                     foreground=[("active", "white"), ("pressed", "white")])
                     
        except Exception:
            pass
        self._build_ui()

    def _build_ui(self):
        # Main container with gradient-like background
        main_container = tk.Frame(self, bg='#ecf0f1', relief='flat', bd=0)
        main_container.pack(fill=tk.BOTH, expand=True, padx=1, pady=1)
        
        # Compact header section
        header_frame = tk.Frame(main_container, bg='#34495e', height=60)
        header_frame.pack(fill=tk.X, pady=(0, 1))
        header_frame.pack_propagate(False)
        
        # Header content
        header_content = tk.Frame(header_frame, bg='#34495e')
        header_content.pack(fill=tk.BOTH, expand=True, padx=12, pady=8)
        
        header = tk.Label(header_content, text="CAP102 Admin Panel", 
                         font=("Segoe UI", 14, "bold"), 
                         fg="white", bg='#34495e')
        header.pack(anchor=tk.W)
        
        subheader = tk.Label(header_content, 
                           text="Maintenance & Development Tools", 
                           font=("Segoe UI", 9), 
                           fg="#bdc3c7", bg='#34495e')
        subheader.pack(anchor=tk.W, pady=(1, 0))
        
        # Content container
        content_frame = tk.Frame(main_container, bg='#ecf0f1')
        content_frame.pack(fill=tk.BOTH, expand=True, padx=8, pady=(8, 0))
        
        # Compact action buttons section - use grid for better space utilization
        actions_frame = tk.LabelFrame(content_frame, text=" Actions ", 
                                    font=("Segoe UI", 10, "bold"),
                                    fg="#2c3e50", bg='#ecf0f1',
                                    relief='flat', bd=1)
        actions_frame.pack(fill=tk.X, pady=(0, 8))
        
        # Buttons container with responsive grid layout
        buttons_container = tk.Frame(actions_frame, bg='#ecf0f1')
        buttons_container.pack(fill=tk.X, padx=8, pady=8)
        
        # Configure grid weights for responsiveness
        buttons_container.grid_columnconfigure(0, weight=1)
        buttons_container.grid_columnconfigure(1, weight=1)
        
        # Separate buttons by type for better organization
        sync_buttons = []
        server_buttons = []
        
        for label, cmd in COMMANDS.items():
            if "Sync" in label or "Predict" in label:
                sync_buttons.append((label, cmd))
            else:
                server_buttons.append((label, cmd))
        
        # Data operations row - responsive grid
        if sync_buttons:
            data_label = tk.Label(buttons_container, text="Data:", 
                                font=("Segoe UI", 9, "bold"),
                                fg="#2c3e50", bg='#ecf0f1')
            data_label.grid(row=0, column=0, columnspan=2, sticky="w", pady=(0, 3))
            
            for i, (label, cmd) in enumerate(sync_buttons):
                # Compact button text for smaller windows
                short_text = label.replace("Resolved to MySQL", "to MySQL").replace("Severity (Mobile)", "Severity")
                
                btn = tk.Button(buttons_container, text=short_text,
                              font=("Segoe UI", 9, "bold"),
                              bg="#3498db", fg="white",
                              relief='flat', bd=0,
                              padx=12, pady=6,
                              cursor="hand2",
                              command=lambda l=label, c=cmd: self.run_command(l, c))
                btn.grid(row=1, column=i, sticky="ew", padx=(0, 4 if i == 0 else 0))
                
                # Hover effects
                def on_enter(e, btn=btn):
                    btn.config(bg="#2980b9")
                def on_leave(e, btn=btn):
                    btn.config(bg="#3498db")
                
                btn.bind("<Enter>", on_enter)
                btn.bind("<Leave>", on_leave)
                
                self.command_buttons.append(btn)
                self.buttons[label] = btn
        
        # Server operations row
        if server_buttons:
            server_label = tk.Label(buttons_container, text="Servers:", 
                                  font=("Segoe UI", 9, "bold"),
                                  fg="#2c3e50", bg='#ecf0f1')
            server_label.grid(row=2, column=0, columnspan=2, sticky="w", pady=(8, 3))
            
            for i, (label, cmd) in enumerate(server_buttons):
                # Compact button text
                short_text = label.replace("Start ", "").replace(" (serve)", "").replace(" (python)", "")
                
                btn = tk.Button(buttons_container, text=short_text,
                              font=("Segoe UI", 9, "bold"),
                              bg="#e74c3c", fg="white",
                              relief='flat', bd=0,
                              padx=12, pady=6,
                              cursor="hand2",
                              command=lambda l=label, c=cmd: self.run_command(l, c))
                btn.grid(row=3, column=i, sticky="ew", padx=(0, 4 if i == 0 else 0))
                
                # Hover effects
                def on_enter(e, btn=btn):
                    btn.config(bg="#c0392b")
                def on_leave(e, btn=btn):
                    btn.config(bg="#e74c3c")
                
                btn.bind("<Enter>", on_enter)
                btn.bind("<Leave>", on_leave)
                
                self.command_buttons.append(btn)
                self.buttons[label] = btn
        
        # Compact output section with controlled height
        output_frame = tk.LabelFrame(content_frame, text=" Console ", 
                                   font=("Segoe UI", 10, "bold"),
                                   fg="#2c3e50", bg='#ecf0f1',
                                   relief='flat', bd=1)
        output_frame.pack(fill=tk.BOTH, expand=True, pady=(0, 6))
        
        # Set a reasonable minimum height for console to ensure status bar is visible
        output_frame.update_idletasks()
        
        # Output area with enhanced styling - controlled expansion
        output_container = tk.Frame(output_frame, bg='#2c3e50', relief='sunken', bd=1)
        output_container.pack(fill=tk.BOTH, expand=True, padx=6, pady=6)
        
        self.output = scrolledtext.ScrolledText(output_container, 
                                              wrap=tk.WORD, 
                                              state=tk.DISABLED, 
                                              font=("Consolas", 9),
                                              bg="#2c3e50",
                                              fg="#ecf0f1",
                                              insertbackground="#ecf0f1",
                                              selectbackground="#34495e",
                                              relief='flat',
                                              bd=0,
                                              height=12)  # Set explicit height to control space
        self.output.pack(fill=tk.BOTH, expand=True, padx=1, pady=1)
        
        # Compact status bar - always visible at bottom
        statusbar = tk.Frame(content_frame, bg='#bdc3c7', height=34, relief='flat', bd=1)
        statusbar.pack(fill=tk.X, side=tk.BOTTOM)  # Force to bottom
        statusbar.pack_propagate(False)
        
        # Status content
        status_content = tk.Frame(statusbar, bg='#bdc3c7')
        status_content.pack(fill=tk.BOTH, expand=True, padx=8, pady=4)
        
        # Left side - compact progress and status
        left_status = tk.Frame(status_content, bg='#bdc3c7')
        left_status.pack(side=tk.LEFT, fill=tk.Y)
        
        self.progress = ttk.Progressbar(left_status, mode="indeterminate", length=80)
        self.progress.pack(side=tk.LEFT, pady=1)
        
        self.status_label = tk.Label(left_status, text="Ready", 
                                   font=("Segoe UI", 8, "bold"),
                                   fg="#2c3e50", bg='#bdc3c7')
        self.status_label.pack(side=tk.LEFT, padx=(6, 0), pady=1)
        
        # Right side - essential control buttons only
        right_controls = tk.Frame(status_content, bg='#bdc3c7')
        right_controls.pack(side=tk.RIGHT, fill=tk.Y)
        
        # Essential control buttons with compact styling
        button_configs = [
            ("Clear", self.clear_output, "#95a5a6"),
            ("Stop All", self.stop_all_current, "#e67e22"),
            ("Stop", self.stop_current, "#e74c3c"),
            ("✕", self.destroy, "#7f8c8d")  # X symbol for exit to save space
        ]
        
        for text, command, color in button_configs:
            btn = tk.Button(right_controls, text=text,
                          font=("Segoe UI", 8, "bold"),
                          bg=color, fg="white",
                          relief='flat', bd=0,
                          padx=8, pady=2,
                          cursor="hand2",
                          command=command)
            btn.pack(side=tk.RIGHT, padx=(4, 0))
            
            # Hover effects for control buttons
            def make_hover(button, original_color):
                def on_enter(e):
                    # Darken color on hover
                    darker_colors = {
                        "#95a5a6": "#7f8c8d",
                        "#e67e22": "#d35400", 
                        "#e74c3c": "#c0392b",
                        "#7f8c8d": "#95a5a6"
                    }
                    button.config(bg=darker_colors.get(original_color, original_color))
                def on_leave(e):
                    button.config(bg=original_color)
                return on_enter, on_leave
            
            enter_func, leave_func = make_hover(btn, color)
            btn.bind("<Enter>", enter_func)
            btn.bind("<Leave>", leave_func)
            
            # Store stop buttons for state management
            if text == "Stop":
                self.stop_btn = btn
                btn.config(state=tk.DISABLED)
            elif text == "Stop All":
                self.stop_all_btn = btn
                btn.config(state=tk.DISABLED)

    def clear_output(self):
        self.output.configure(state=tk.NORMAL)
        self.output.delete(1.0, tk.END)
        self.output.configure(state=tk.DISABLED)

    def append_output(self, text: str):
        self.output.configure(state=tk.NORMAL)
        self.output.insert(tk.END, text)
        self.output.see(tk.END)
        self.output.configure(state=tk.DISABLED)

    def run_command(self, label, cmd):
        # Ensure working directory is repo root so artisan works
        cwd = REPO_ROOT
        if not os.path.exists(PHP_ARTISAN):
            messagebox.showerror("Error", f"artisan not found in {REPO_ROOT}")
            return
        # On Windows, use shell=False and pass list
        def worker():
            long_running = label in LONG_RUNNING_LABELS
            self.set_running(True, cmd, scope=("single" if long_running else "global"), label=label)
            self.append_output(f"> {' '.join(cmd)}\n\n")
            try:
                proc = subprocess.Popen(cmd, cwd=cwd, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, shell=False)
                self.current_process = proc
                self.current_label = label
                # track process by label, disable only that button
                self.processes[label] = proc
                try:
                    # Ensure the button for this label stays disabled while running
                    if label in self.buttons:
                        self.buttons[label].configure(state=tk.DISABLED)
                except Exception:
                    pass
                for line in iter(proc.stdout.readline, b''):
                    if not line:
                        break
                    try:
                        decoded = line.decode('utf-8', errors='ignore')
                    except Exception:
                        decoded = str(line)
                    self.append_output(decoded)
                proc.wait()
                self.append_output(f"\n[exit code {proc.returncode}]\n\n")
            except FileNotFoundError as e:
                missing = cmd[0] if isinstance(cmd, (list, tuple)) and cmd else 'executable'
                self.append_output(f"Error: {e}. Ensure '{missing}' is installed and in PATH.\n")
            except Exception as e:
                self.append_output(f"Unexpected error: {e}\n")
            finally:
                # cleanup
                try:
                    if label in self.processes and self.processes[label] is proc:
                        del self.processes[label]
                except Exception:
                    pass
                try:
                    # Re-enable the button if no other process with same label (avoid duplicates)
                    if label in self.buttons:
                        self.buttons[label].configure(state=tk.NORMAL if label not in self.processes else tk.DISABLED)
                except Exception:
                    pass
                # If no more running processes, clear current pointers
                if not self.processes:
                    self.current_process = None
                    self.current_label = None
                self.set_running(False, cmd, scope=("single" if long_running else "global"), label=label)
        threading.Thread(target=worker, daemon=True).start()

    def stop_current(self):
        # Attempt to stop the currently running process (and its children on Windows)
        proc = self.current_process
        if proc is None or proc.poll() is not None:
            return
        try:
            self.append_output("\nStopping current process...\n")
            if os.name == 'nt':
                # Kill process tree on Windows
                subprocess.run(["taskkill", "/F", "/T", "/PID", str(proc.pid)], capture_output=True)
            else:
                proc.terminate()
        except Exception as e:
            self.append_output(f"Stop failed: {e}\n")

    def stop_all_current(self):
        if not self.processes:
            return
        self.append_output("\nStopping all running processes...\n")
        for label, proc in list(self.processes.items()):
            try:
                if proc and proc.poll() is None:
                    if os.name == 'nt':
                        subprocess.run(["taskkill", "/F", "/T", "/PID", str(proc.pid)], capture_output=True)
                    else:
                        proc.terminate()
            except Exception as e:
                self.append_output(f"Stop failed for {label}: {e}\n")
        self.processes.clear()
        self.current_process = None
        self.current_label = None
        # Re-enable all buttons
        for label, b in self.buttons.items():
            if b.winfo_exists():  # Check if button still exists
                b.configure(state=tk.NORMAL)

    def set_running(self, is_running: bool, cmd=None, scope: str = "global", label: str | None = None):
        self.running = is_running
        if is_running:
            self.title("Admin Control Panel • Running…")
            if scope == "single" and label:
                self.status_label.configure(text=f"Running: {' '.join(cmd) if cmd else ''}")
                # Only disable the button for this label
                try:
                    if label in self.buttons:
                        self.buttons[label].configure(state=tk.DISABLED)
                except Exception:
                    pass
                # Start spinner if this is the first active process
                if len(self.processes) <= 1:
                    try:
                        self.progress.start(12)
                    except Exception:
                        pass
            else:
                self.status_label.configure(text=f"Running: {' '.join(cmd) if cmd else ''}")
                try:
                    self.progress.start(12)
                except Exception:
                    pass
                for b in self.command_buttons:
                    if b.winfo_exists():  # Check if button still exists
                        b.configure(state=tk.DISABLED)
            # Stop buttons
            if hasattr(self, 'stop_btn') and self.stop_btn.winfo_exists():
                self.stop_btn.configure(state=tk.NORMAL)
            if hasattr(self, 'stop_all_btn') and self.stop_all_btn.winfo_exists():
                self.stop_all_btn.configure(state=tk.NORMAL if self.processes else tk.DISABLED)
        else:
            # When stopping, keep other running labels disabled if they still run
            if scope == "single" and label:
                # Re-enable only if no process for that label remains
                try:
                    if label in self.buttons and label not in self.processes:
                        self.buttons[label].configure(state=tk.NORMAL)
                except Exception:
                    pass
                # Spinner: stop if no processes left, else keep spinning
                if not self.processes:
                    self.title("Admin Control Panel")
                    self.status_label.configure(text="Ready")
                    try:
                        self.progress.stop()
                    except Exception:
                        pass
            else:
                # Global stop: re-enable all, except those still running
                self.title("Admin Control Panel")
                self.status_label.configure(text="Ready" if not self.processes else "Running…")
                try:
                    if not self.processes:
                        self.progress.stop()
                except Exception:
                    pass
                for lbl, b in self.buttons.items():
                    if b.winfo_exists():  # Check if button still exists
                        b.configure(state=(tk.DISABLED if lbl in self.processes else tk.NORMAL))
            # Update stop buttons state
            if hasattr(self, 'stop_btn') and self.stop_btn.winfo_exists():
                self.stop_btn.configure(state=(tk.NORMAL if self.processes else tk.DISABLED))
            if hasattr(self, 'stop_all_btn') and self.stop_all_btn.winfo_exists():
                self.stop_all_btn.configure(state=(tk.NORMAL if self.processes else tk.DISABLED))

if __name__ == "__main__":
    # Optional: allow pythonw.exe to run without console
    app = AdminControlPanel()
    app.mainloop()
