import { useState, useEffect } from "react";
import { useNavigate, Link } from "react-router-dom";

import axios from "../lib/axios";
import AutenticacionDosPasos from "./AutenticacionDosPasos";
import "./LoginConDosPasos.css";

export default function LoginConDosPasos({
    setAuth,
    setPermisos,
    setPendingTwoFactor,
}) {
    const navigate = useNavigate();
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");
    const [mostrarDosPasos, setMostrarDosPasos] = useState(false);
    const [usuarioName, setUsuarioName] = useState("");
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        const limpiarEstado = async () => {
            setMostrarDosPasos(false);
            setUsuarioName("");
            setErrors({});
            if (setPendingTwoFactor) {
                setPendingTwoFactor(false);
            }

            try {
                await axios.post("/api/logout");
            } catch (error) {
                // Silenciar error de logout
            }
        };

        limpiarEstado();
    }, [setPendingTwoFactor]);

    const extraerRutasDesdePermisos = (permisos) => {
        if (!Array.isArray(permisos)) return [];

        if (typeof permisos[0] === "string") {
            return permisos;
        }

        return permisos.map((p) => p.ruta).filter(Boolean);
    };

    const validateUsername = (value) => {
        const newErrors = { ...errors };
        const cleanValue = value.trim();

        if (!cleanValue) {
            newErrors.username = "El usuario o correo es obligatorio";
        } else if (cleanValue.length < 3) {
            newErrors.username = "Debe tener al menos 3 caracteres";
        } else {
            // Eliminamos el ELSE IF del regex estricto para permitir @ y .
            delete newErrors.username;
        }

        setErrors(newErrors);
        return !newErrors.username;
    };

    const validatePassword = (value) => {
        const newErrors = { ...errors };

        if (!value) {
            newErrors.password = "La contraseña es obligatoria";
        } else if (value.length > 255) {
            newErrors.password =
                "La contraseña no puede exceder 255 caracteres";
        } else {
            delete newErrors.password;
        }

        setErrors(newErrors);
        return !newErrors.password;
    };

    const validateAllFields = () => {
        const usernameValid = validateUsername(username);
        const passwordValid = validatePassword(password);
        return usernameValid && passwordValid;
    };

    const handleLogin = async (e) => {
        e.preventDefault();

        if (!validateAllFields()) return;

        setLoading(true);
        setErrors({});

        try {
            await axios.get("/sanctum/csrf-cookie");
            const loginResponse = await axios.post(
                "/api/login",
                { name: username.trim(), password },
                { withCredentials: true }
            );

            if (loginResponse.data.error) {
                setErrors({ general: loginResponse.data.error });
                setLoading(false);
                return;
            }

            const res = await axios.get("/api/user");
            const permisos = res.data.permisos;
            const rutas = extraerRutasDesdePermisos(permisos);

            setUsuarioName(res.data.name);
            setMostrarDosPasos(true);
            if (setPendingTwoFactor) {
                setPendingTwoFactor(true);
            }
        } catch (err) {
            if (err.response) {
                if (err.response.status === 403) {
                    setErrors({
                        general:
                            "Tu cuenta está desactivada, contacta al administrador",
                    });
                } else if (err.response.status === 422) {
                    const errorMessage = err.response.data.message;
                    if (
                        errorMessage.includes("username") ||
                        errorMessage.includes("usuario")
                    ) {
                        setErrors({
                            username: "El nombre de usuario no existe",
                        });
                    } else if (
                        errorMessage.includes("credenciales") ||
                        errorMessage.includes("password")
                    ) {
                        setErrors({
                            general:
                                "El usuario o la contraseña no son correctos.",
                        });
                    } else {
                        setErrors({
                            general:
                                "El usuario o la contraseña no son correctos.",
                        });
                    }
                } else if (err.response.status === 429) {
                    setErrors({
                        general:
                            "Demasiados intentos fallidos, intenta en 15 minutos",
                    });
                } else {
                    setErrors({
                        general:
                            err.response.data.message ||
                            "Error al iniciar sesión",
                    });
                }
            } else {
                setErrors({
                    general:
                        "Error de conexión. Verifica tu conexión a internet",
                });
            }
        } finally {
            setLoading(false);
        }
    };

    const completarLogin = (rutas) => {
        setAuth(true);
        setPermisos(rutas);

        if (setPendingTwoFactor) {
            setPendingTwoFactor(false);
        }

        if (rutas.includes("/admin")) {
            navigate("/admin");
        } else if (rutas.includes("/dashboard")) {
            navigate("/dashboard");
        } else {
            navigate("/unauthorized");
        }
    };

    const manejarVerificacionExitosa = async () => {
        try {
            const res = await axios.get("/api/user");
            const permisos = res.data.permisos;
            const rutas = extraerRutasDesdePermisos(permisos);
            completarLogin(rutas);
        } catch (error) {
            setErrors({ general: "Error al cargar los datos del usuario" });
            navigate("/login");
        }
    };

    const manejarCancelarDosPasos = async () => {
        setMostrarDosPasos(false);
        setUsuarioName("");
        setErrors({});
        if (setPendingTwoFactor) {
            setPendingTwoFactor(false);
        }

        try {
            await axios.post("/api/logout");
        } catch (error) {
            // Silenciar error de logout
        }
    };

    const handleUsernameChange = (e) => {
        const value = e.target.value;
        setUsername(value);
        validateUsername(value);

        // Limpiar error general cuando el usuario modifica el campo
        if (errors.general) {
            setErrors((prev) => {
                const newErrors = { ...prev };
                delete newErrors.general;
                return newErrors;
            });
        }

        if (mostrarDosPasos) {
            setMostrarDosPasos(false);
            setUsuarioName("");
            setErrors({});
            if (setPendingTwoFactor) {
                setPendingTwoFactor(false);
            }
        }
    };

    const handlePasswordChange = (e) => {
        const value = e.target.value;
        setPassword(value);
        validatePassword(value);

        // Limpiar error general cuando el usuario modifica el campo
        if (errors.general) {
            setErrors((prev) => {
                const newErrors = { ...prev };
                delete newErrors.general;
                return newErrors;
            });
        }
    };

    const isFormValid = () => {
        const hasRequiredFields = username.trim() && password;
        const hasNoErrors = Object.keys(errors).length === 0;
        return hasRequiredFields && hasNoErrors;
    };

    if (mostrarDosPasos) {
        return (
            <AutenticacionDosPasos
                onVerificacionExitosa={manejarVerificacionExitosa}
                usuarioName={usuarioName}
                onCancelar={manejarCancelarDosPasos}
            />
        );
    }

    return (
        <div className="login-container">
            <div className="login-card">
                <h2 className="login-title">Iniciar Sesión</h2>

                {errors.general && (
                    <div className="error-message general-error">
                        {errors.general}
                    </div>
                )}

                <form onSubmit={handleLogin} className="login-form">
                    <div className="form-group">
                        <label className="form-label">
                            Correo / Nombre de Usuario{" "}
                            <span className="required">*</span>
                        </label>
                        <input
                            type="text"
                            placeholder="Ingrese su nombre o correo electronico"
                            value={username}
                            onChange={handleUsernameChange}
                            className={`form-input ${
                                errors.username ? "error" : ""
                            }`}
                            required
                        />
                        {errors.username && (
                            <div className="error-message">
                                {errors.username}
                            </div>
                        )}
                    </div>

                    <div className="form-group">
                        <label className="form-label">
                            Contraseña <span className="required">*</span>
                        </label>
                        <input
                            type="password"
                            placeholder="Ingrese su contraseña"
                            value={password}
                            onChange={handlePasswordChange}
                            className={`form-input ${
                                errors.password ? "error" : ""
                            }`}
                            required
                        />
                        {errors.password && (
                            <div className="error-message">
                                {errors.password}
                            </div>
                        )}
                    </div>
          <button 
            type="submit" 
            className="login-btn"
            disabled={loading || !isFormValid()}
          >
            {loading ? 'Iniciando sesión...' : 'Entrar'}
          </button>
        </form>

        <div className="login-links">
          <label
            className="forgot-password-link"
            onClick={() => {
                navigate('/recuperar-contrasena')
            }}
          >
            ¿Olvidaste tu contraseña?
          </label>

          <p className="register-link">
            ¿No tienes cuenta? <Link to="/registro">Regístrate aquí</Link>
          </p>
        </div>
      </div>
    </div>
  )
}